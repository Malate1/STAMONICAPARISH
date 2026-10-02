<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Archive extends Role_Controller
{
    private $record_types = ['baptism','confirmation','communion','marriage','funeral'];

    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
    }

    public function index()
    {
        $data['schema_ready'] = $this->schema_ready();
        $data['workflow_ready'] = $this->record_workflow_ready();
        $data['batches'] = [];
        $data['pages_by_batch'] = [];

        if ($data['schema_ready']) {
            $data['batches'] = $this->db
                ->select('archive_batches.*, creator.first_name AS creator_first_name, creator.last_name AS creator_last_name,
                    COUNT(DISTINCT sacramental_records.id) AS record_count,
                    COUNT(DISTINCT archive_pages.id) AS page_count')
                ->from('archive_batches')
                ->join('users AS creator', 'creator.id = archive_batches.created_by', 'left')
                ->join('sacramental_records', 'sacramental_records.archive_batch_id = archive_batches.id', 'left')
                ->join('archive_pages', 'archive_pages.batch_id = archive_batches.id', 'left')
                ->group_by('archive_batches.id')
                ->order_by("FIELD(archive_batches.status,'encoding','review','completed')", '', false)
                ->order_by('archive_batches.created_at', 'desc')
                ->get()->result_array();

            $pages = $this->db->order_by('created_at', 'desc')->get('archive_pages')->result_array();
            foreach ($pages as $page) {
                $data['pages_by_batch'][(int) $page['batch_id']][] = $page;
            }
        }

        $this->render_app('admin/archive_list', $data, 'layouts/app_admin');
    }

    public function batch_store()
    {
        if (!$this->schema_ready()) return $this->migration_required();

        $this->form_validation->set_rules('name', 'Batch Name', 'required|max_length[180]');
        if ($this->form_validation->run() === false) {
            return $this->json(['success'=>false,'message'=>strip_tags(validation_errors())]);
        }

        $record_type = trim((string) $this->input->post('record_type', true));
        if ($record_type !== '' && !in_array($record_type, $this->record_types, true)) {
            return $this->json(['success'=>false,'message'=>'Select a valid sacramental record type.']);
        }

        $year_from = $this->normalize_year($this->input->post('year_from'));
        $year_to = $this->normalize_year($this->input->post('year_to'));
        if ($year_from && $year_to && $year_to < $year_from) {
            return $this->json(['success'=>false,'message'=>'Year To cannot be earlier than Year From.']);
        }

        $id = (int) $this->input->post('id');
        $payload = [
            'name' => trim((string) $this->input->post('name', true)),
            'record_type' => $record_type ?: null,
            'source_label' => trim((string) $this->input->post('source_label', true)) ?: null,
            'physical_location' => trim((string) $this->input->post('physical_location', true)) ?: null,
            'year_from' => $year_from,
            'year_to' => $year_to,
            'notes' => trim((string) $this->input->post('notes', true)) ?: null,
        ];

        if ($id) {
            $existing = $this->db->get_where('archive_batches', ['id'=>$id])->row_array();
            if (!$existing) return $this->json(['success'=>false,'message'=>'Archive batch not found.'],404);
            if ($existing['status'] === 'completed') {
                return $this->json(['success'=>false,'message'=>'Completed archive batches are locked. Reopen the batch before editing metadata.']);
            }
            $this->db->where('id',$id)->update('archive_batches',$payload);
            $message = 'Archive batch updated.';
        } else {
            $payload['batch_code'] = 'ARC-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $payload['status'] = 'encoding';
            $payload['created_by'] = $this->current_user['id'];
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('archive_batches',$payload);
            $id = $this->db->insert_id();
            $message = 'Archive batch created. You can now upload register pages and import or encode records.';
        }

        $this->log_activity('Saved legacy archive batch','archive',$payload['name']);
        $this->json(['success'=>true,'message'=>$message,'id'=>(int)$id]);
    }

    public function batch_status($id)
    {
        if (!$this->schema_ready()) return $this->migration_required();

        $batch = $this->db->get_where('archive_batches',['id'=>(int)$id])->row_array();
        if (!$batch) return $this->json(['success'=>false,'message'=>'Archive batch not found.'],404);

        $new_status = $this->input->post('status',true);
        $allowed = [
            'encoding' => ['review'],
            'review' => ['encoding','completed'],
            'completed' => ['review'],
        ];
        if (!isset($allowed[$batch['status']]) || !in_array($new_status,$allowed[$batch['status']],true)) {
            return $this->json(['success'=>false,'message'=>'That archive batch status change is not allowed.']);
        }

        if ($new_status === 'completed') {
            $draft_count = (int) $this->db->where('archive_batch_id',(int)$id)
                ->where('record_status','draft')
                ->count_all_results('sacramental_records');
            if ($draft_count > 0) {
                return $this->json([
                    'success'=>false,
                    'message'=>'This batch still has ' . $draft_count . ' Draft record(s). Verify them before marking the batch Completed.'
                ]);
            }
        }

        $update = ['status'=>$new_status];
        if ($new_status === 'completed') {
            $update['completed_by'] = $this->current_user['id'];
            $update['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($batch['status'] === 'completed') {
            $update['completed_by'] = null;
            $update['completed_at'] = null;
        }

        $this->db->where('id',(int)$id)->update('archive_batches',$update);
        $this->log_activity('Changed archive batch status','archive',$batch['batch_code'] . ' → ' . $new_status);
        $this->json(['success'=>true,'message'=>'Archive batch moved to ' . status_label($new_status) . '.']);
    }

    public function page_upload()
    {
        if (!$this->schema_ready()) return $this->migration_required();

        $batch_id = (int) $this->input->post('batch_id');
        $batch = $this->db->get_where('archive_batches',['id'=>$batch_id])->row_array();
        if (!$batch) return $this->json(['success'=>false,'message'=>'Archive batch not found.']);
        if ($batch['status'] === 'completed') {
            return $this->json(['success'=>false,'message'=>'Reopen this completed batch before adding source pages.']);
        }
        if (empty($_FILES['source_page']['name'])) {
            return $this->json(['success'=>false,'message'=>'Choose a scanned register page (JPG, PNG or PDF).']);
        }

        $this->load->library('secure_upload');
        $stored = $this->secure_upload->store(
            $_FILES['source_page'],
            FCPATH . UPLOAD_ARCHIVE,
            'archive_' . $batch_id,
            8 * 1024 * 1024
        );
        if (!$stored['success']) return $this->json(['success'=>false,'message'=>$stored['message']]);

        $this->db->insert('archive_pages',[
            'batch_id'=>$batch_id,
            'registry_book'=>trim((string)$this->input->post('registry_book',true)) ?: null,
            'registry_page'=>trim((string)$this->input->post('registry_page',true)) ?: null,
            'page_label'=>trim((string)$this->input->post('page_label',true)) ?: null,
            'file_name'=>$stored['filename'],
            'original_name'=>$stored['original_name'],
            'file_path'=>UPLOAD_ARCHIVE . $stored['filename'],
            'mime_type'=>$stored['mime_type'],
            'notes'=>trim((string)$this->input->post('notes',true)) ?: null,
            'uploaded_by'=>$this->current_user['id'],
            'created_at'=>date('Y-m-d H:i:s'),
        ]);

        $this->log_activity('Uploaded legacy register page','archive',$batch['batch_code']);
        $this->json(['success'=>true,'message'=>'Register source page uploaded securely.']);
    }

    public function page_delete($id)
    {
        if (!$this->schema_ready()) return $this->migration_required();

        $page = $this->db->select('archive_pages.*, archive_batches.status AS batch_status, archive_batches.batch_code')
            ->from('archive_pages')
            ->join('archive_batches','archive_batches.id = archive_pages.batch_id')
            ->where('archive_pages.id',(int)$id)
            ->get()->row_array();
        if (!$page) return $this->json(['success'=>false,'message'=>'Archive page not found.'],404);
        if ($page['batch_status'] === 'completed') {
            return $this->json(['success'=>false,'message'=>'Reopen the completed batch before removing a source scan.']);
        }

        $linked = (int) $this->db->where('archive_page_id',(int)$id)->count_all_results('sacramental_records');
        if ($linked > 0) {
            return $this->json([
                'success'=>false,
                'message'=>'This source page is linked to ' . $linked . ' sacramental record(s). Keep the source scan or relink those records first.'
            ]);
        }

        $file = FCPATH . ltrim((string)$page['file_path'],'/\\');
        $this->db->where('id',(int)$id)->delete('archive_pages');
        if (is_file($file) && realpath(dirname($file)) === realpath(FCPATH . UPLOAD_ARCHIVE)) @unlink($file);

        $this->log_activity('Deleted legacy register page','archive',$page['batch_code']);
        $this->json(['success'=>true,'message'=>'Source page removed.']);
    }

    public function import_csv()
    {
        if (!$this->schema_ready()) return $this->migration_required();
        if (!$this->record_workflow_ready()) {
            return $this->json([
                'success'=>false,
                'message'=>'Run database/migrations/20260925_sacramental_record_workflow.sql before importing legacy records.'
            ]);
        }

        $batch_id = (int) $this->input->post('batch_id');
        $batch = $this->db->get_where('archive_batches',['id'=>$batch_id])->row_array();
        if (!$batch) return $this->json(['success'=>false,'message'=>'Archive batch not found.']);
        if ($batch['status'] !== 'encoding') {
            return $this->json(['success'=>false,'message'=>'CSV import is allowed only while the batch is in Encoding status.']);
        }

        if (empty($_FILES['csv_file']['name']) || (int)$_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['success'=>false,'message'=>'Choose a CSV file to import.']);
        }
        if ((int)$_FILES['csv_file']['size'] > 5 * 1024 * 1024) {
            return $this->json(['success'=>false,'message'=>'CSV file must be 5 MB or less.']);
        }

        $extension = strtolower(pathinfo($_FILES['csv_file']['name'],PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            return $this->json(['success'=>false,'message'=>'Export the spreadsheet as CSV before importing.']);
        }

        $handle = fopen($_FILES['csv_file']['tmp_name'],'r');
        if (!$handle) return $this->json(['success'=>false,'message'=>'The CSV file could not be read.']);

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return $this->json(['success'=>false,'message'=>'CSV header row is missing.']);
        }

        $headers = array_map(function($value){
            return strtolower(trim(preg_replace('/[^a-z0-9]+/i','_',(string)$value),'_'));
        },$headers);

        $required = ['full_name','sacrament_date'];
        foreach ($required as $field) {
            if (!in_array($field,$headers,true)) {
                fclose($handle);
                return $this->json(['success'=>false,'message'=>'CSV must include "' . $field . '" column.']);
            }
        }

        $pages = $this->db->where('batch_id',$batch_id)->get('archive_pages')->result_array();
        $page_lookup = [];
        foreach ($pages as $page) {
            $key = strtolower(trim((string)$page['registry_book'])) . '|' . strtolower(trim((string)$page['registry_page']));
            if ($key !== '|') $page_lookup[$key] = (int)$page['id'];
        }

        $inserted = 0;
        $duplicates = 0;
        $invalid = 0;
        $row_number = 1;

        $this->db->trans_start();

        while (($row = fgetcsv($handle)) !== false) {
            $row_number++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;

            $row = array_pad($row,count($headers),'');
            $data = array_combine($headers,array_slice($row,0,count($headers)));
            if (!$data) { $invalid++; continue; }

            $record_type = strtolower(trim((string)($data['record_type'] ?? $batch['record_type'] ?? '')));
            if (!in_array($record_type,$this->record_types,true)) {
                $invalid++;
                continue;
            }

            $full_name = trim((string)($data['full_name'] ?? ''));
            $sacrament_date = $this->normalize_date($data['sacrament_date'] ?? '');
            $birth_date = $this->normalize_date($data['birth_date'] ?? '');
            if ($full_name === '' || !$sacrament_date) {
                $invalid++;
                continue;
            }

            $registry_book = trim((string)($data['registry_book'] ?? '')) ?: null;
            $registry_page = trim((string)($data['registry_page'] ?? '')) ?: null;
            $registry_entry_no = trim((string)($data['registry_entry_no'] ?? '')) ?: null;

            if ($this->is_duplicate_record($record_type,$full_name,$sacrament_date,$registry_book,$registry_page,$registry_entry_no)) {
                $duplicates++;
                continue;
            }

            $page_key = strtolower((string)$registry_book) . '|' . strtolower((string)$registry_page);
            $archive_page_id = $page_lookup[$page_key] ?? null;

            $this->db->insert('sacramental_records',[
                'record_type'=>$record_type,
                'full_name'=>$full_name,
                'birth_date'=>$birth_date,
                'sacrament_date'=>$sacrament_date,
                'father_name'=>trim((string)($data['father_name'] ?? '')) ?: null,
                'mother_name'=>trim((string)($data['mother_name'] ?? '')) ?: null,
                'spouse_name'=>trim((string)($data['spouse_name'] ?? '')) ?: null,
                'minister_name'=>trim((string)($data['minister_name'] ?? '')) ?: null,
                'registry_book'=>$registry_book,
                'registry_page'=>$registry_page,
                'registry_entry_no'=>$registry_entry_no,
                'remarks'=>trim((string)($data['remarks'] ?? '')) ?: null,
                'record_status'=>'draft',
                'source_type'=>'legacy_import',
                'archive_batch_id'=>$batch_id,
                'archive_page_id'=>$archive_page_id,
                'created_by'=>$this->current_user['id'],
                'updated_by'=>$this->current_user['id'],
                'created_at'=>date('Y-m-d H:i:s'),
            ]);
            $inserted++;
        }

        fclose($handle);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->json(['success'=>false,'message'=>'The CSV import failed and was rolled back.']);
        }

        $this->log_activity('Imported legacy sacramental records','archive',$batch['batch_code'] . ' · ' . $inserted . ' inserted');

        $this->json([
            'success'=>true,
            'message'=>'Import finished: ' . $inserted . ' Draft record(s) added, ' . $duplicates . ' duplicate(s) skipped, ' . $invalid . ' invalid row(s) skipped.',
            'inserted'=>$inserted,
            'duplicates'=>$duplicates,
            'invalid'=>$invalid,
        ]);
    }

    public function template()
    {
        $filename = 'sta_monica_legacy_records_template.csv';
        $headers = [
            'record_type','full_name','birth_date','sacrament_date','father_name','mother_name',
            'spouse_name','minister_name','registry_book','registry_page','registry_entry_no','remarks'
        ];

        $this->output
            ->set_content_type('text/csv','utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"');

        $stream = fopen('php://temp','r+');
        fputcsv($stream,$headers);
        fputcsv($stream,['baptism','Juan Dela Cruz','1950-01-10','1950-02-15','Pedro Dela Cruz','Maria Santos','','Fr. Example','B-1950','12','34','Example row - replace or delete']);
        rewind($stream);
        $this->output->set_output(stream_get_contents($stream));
        fclose($stream);
    }

    private function schema_ready()
    {
        return $this->db->table_exists('archive_batches')
            && $this->db->table_exists('archive_pages')
            && $this->db->field_exists('archive_batch_id','sacramental_records')
            && $this->db->field_exists('archive_page_id','sacramental_records');
    }

    private function record_workflow_ready()
    {
        return $this->db->field_exists('record_status','sacramental_records')
            && $this->db->field_exists('source_type','sacramental_records')
            && $this->db->field_exists('updated_by','sacramental_records');
    }

    private function migration_required()
    {
        return $this->json([
            'success'=>false,
            'message'=>'Run database/migrations/20260925_sacramental_record_workflow.sql, then database/migrations/20260925_legacy_archive.sql.'
        ]);
    }

    private function normalize_year($value)
    {
        if ($value === null || $value === '') return null;
        $year = (int)$value;
        return ($year >= 1500 && $year <= (int)date('Y')) ? $year : null;
    }

    private function normalize_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') return null;

        foreach (['Y-m-d','m/d/Y','d/m/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format,$value);
            if ($date && $date->format($format) === $value) return $date->format('Y-m-d');
        }
        return null;
    }

    private function is_duplicate_record($type,$name,$date,$book,$page,$entry)
    {
        if ($book && $page && $entry) {
            return $this->db->where('record_type',$type)
                ->where('registry_book',$book)
                ->where('registry_page',$page)
                ->where('registry_entry_no',$entry)
                ->count_all_results('sacramental_records') > 0;
        }

        return $this->db->where('record_type',$type)
            ->where('full_name',$name)
            ->where('sacrament_date',$date)
            ->count_all_results('sacramental_records') > 0;
    }
}

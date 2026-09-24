<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Community extends Role_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->guard([ROLE_ADMIN, ROLE_SECRETARY]);
        $this->load->model('Community_model');
    }

    public function index()
    {
        $data['schema_ready'] = $this->Community_model->schema_ready();
        $data['chapels'] = $data['schema_ready'] ? $this->Community_model->chapels(false) : [];
        $data['clusters'] = $data['schema_ready'] ? $this->Community_model->clusters(null, false) : [];
        $data['parish_officials'] = $data['schema_ready'] ? $this->Community_model->officials('parish', null, null, false) : [];

        if ($data['schema_ready']) {
            foreach ($data['chapels'] as &$chapel) {
                $chapel['mass_schedules'] = $this->Community_model->chapel_mass_schedules($chapel['id'], false);
                $chapel['officials'] = $this->Community_model->officials('chapel', $chapel['id'], null, false);
            }
            unset($chapel);

            foreach ($data['clusters'] as &$cluster) {
                $cluster['officials'] = $this->Community_model->officials('cluster', $cluster['chapel_id'], $cluster['id'], false);
            }
            unset($cluster);
        }

        $this->render_app('admin/community_manage', $data, 'layouts/app_admin');
    }

    public function chapel_get($id)
    {
        $item = $this->Community_model->chapel((int)$id);
        if (!$item) return $this->json(['success'=>false,'message'=>'Chapel not found.'],404);
        $item['cover_url'] = !empty($item['cover_image']) ? base_url($item['cover_image']) : null;
        $this->json(['success'=>true,'data'=>$item]);
    }

    public function chapel_store()
    {
        if (!$this->Community_model->schema_ready()) return $this->migration_required();

        $this->form_validation->set_rules('name','Chapel Name','required|max_length[180]');
        if ($this->form_validation->run() === false) {
            return $this->json(['success'=>false,'message'=>strip_tags(validation_errors())]);
        }

        $id = (int)$this->input->post('id');
        $existing = $id ? $this->Community_model->chapel($id) : null;
        if ($id && !$existing) return $this->json(['success'=>false,'message'=>'Chapel not found.'],404);

        $payload = [
            'name' => trim((string)$this->input->post('name',true)),
            'patron_saint' => trim((string)$this->input->post('patron_saint',true)) ?: null,
            'description' => $this->input->post('description'),
            'address' => trim((string)$this->input->post('address',true)) ?: null,
            'barangay' => trim((string)$this->input->post('barangay',true)) ?: null,
            'location_notes' => trim((string)$this->input->post('location_notes',true)) ?: null,
            'contact_number' => trim((string)$this->input->post('contact_number',true)) ?: null,
            'feast_date' => trim((string)$this->input->post('feast_date',true)) ?: null,
            'latitude' => $this->input->post('latitude') !== '' ? $this->input->post('latitude',true) : null,
            'longitude' => $this->input->post('longitude') !== '' ? $this->input->post('longitude',true) : null,
            'display_order' => max(0,(int)$this->input->post('display_order')),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ];

        $upload = $this->upload_image('cover_image','chapels','chapel');
        if (!$upload['success']) return $this->json(['success'=>false,'message'=>$upload['message']]);
        if ($upload['path']) $payload['cover_image'] = $upload['path'];

        if ($id) {
            $this->Community_model->save_chapel($id,$payload);
            if ($upload['path'] && !empty($existing['cover_image'])) $this->delete_local_image($existing['cover_image'],'uploads/chapels/');
            $message = 'Chapel updated.';
        } else {
            $slug = url_title($payload['name'],'-',true);
            if ($this->db->where('slug',$slug)->count_all_results('chapels') > 0) {
                $slug .= '-' . substr(bin2hex(random_bytes(3)),0,6);
            }
            $payload['slug'] = $slug;
            $payload['created_by'] = $this->current_user['id'];
            $id = $this->Community_model->save_chapel(0,$payload);
            $message = 'Chapel added.';
        }

        $this->json(['success'=>true,'message'=>$message,'id'=>(int)$id]);
    }

    public function chapel_delete($id)
    {
        $chapel = $this->Community_model->chapel((int)$id);
        if (!$chapel) return $this->json(['success'=>false,'message'=>'Chapel not found.'],404);

        $linked_officials = $this->Community_model->officials(null, (int)$id, null, false);

        if ($this->Community_model->delete_chapel((int)$id)) {
            if (!empty($chapel['cover_image'])) $this->delete_local_image($chapel['cover_image'],'uploads/chapels/');
            foreach ($linked_officials as $official) {
                if (!empty($official['photo'])) $this->delete_local_image($official['photo'],'uploads/officials/');
            }
            return $this->json(['success'=>true,'message'=>'Chapel and its linked schedules, clusters and officials were deleted.']);
        }
        $this->json(['success'=>false,'message'=>'Chapel could not be deleted.']);
    }

    public function mass_get($id)
    {
        $item = $this->Community_model->mass_schedule((int)$id);
        if (!$item) return $this->json(['success'=>false,'message'=>'Mass schedule not found.'],404);
        $this->json(['success'=>true,'data'=>$item]);
    }

    public function mass_store()
    {
        if (!$this->Community_model->schema_ready()) return $this->migration_required();

        $chapel_id = (int)$this->input->post('chapel_id');
        if (!$this->Community_model->chapel($chapel_id)) {
            return $this->json(['success'=>false,'message'=>'Select a valid chapel.']);
        }

        $day = (int)$this->input->post('day_of_week');
        if ($day < 0 || $day > 6) return $this->json(['success'=>false,'message'=>'Select a valid day of the week.']);

        $time = $this->input->post('mass_time',true);
        if (!$time) return $this->json(['success'=>false,'message'=>'Mass time is required.']);

        $id = (int)$this->input->post('id');
        $payload = [
            'chapel_id'=>$chapel_id,
            'day_of_week'=>$day,
            'mass_time'=>$time,
            'title'=>trim((string)$this->input->post('title',true)) ?: 'Holy Mass',
            'language'=>trim((string)$this->input->post('language',true)) ?: null,
            'recurrence_note'=>trim((string)$this->input->post('recurrence_note',true)) ?: null,
            'notes'=>trim((string)$this->input->post('notes',true)) ?: null,
            'display_order'=>max(0,(int)$this->input->post('display_order')),
            'is_active'=>$this->input->post('is_active') ? 1 : 0,
        ];
        $saved = $this->Community_model->save_mass_schedule($id,$payload);
        $this->json(['success'=>(bool)$saved,'message'=>$id?'Mass schedule updated.':'Mass schedule added.']);
    }

    public function mass_delete($id)
    {
        $this->json([
            'success'=>(bool)$this->Community_model->delete_mass_schedule((int)$id),
            'message'=>'Mass schedule removed.'
        ]);
    }

    public function cluster_get($id)
    {
        $item = $this->Community_model->cluster((int)$id);
        if (!$item) return $this->json(['success'=>false,'message'=>'GSK cluster not found.'],404);
        $this->json(['success'=>true,'data'=>$item]);
    }

    public function cluster_store()
    {
        if (!$this->Community_model->schema_ready()) return $this->migration_required();

        $chapel_id = (int)$this->input->post('chapel_id');
        if (!$this->Community_model->chapel($chapel_id)) {
            return $this->json(['success'=>false,'message'=>'Select a valid chapel.']);
        }

        $this->form_validation->set_rules('name','Cluster Name','required|max_length[180]');
        if ($this->form_validation->run() === false) {
            return $this->json(['success'=>false,'message'=>strip_tags(validation_errors())]);
        }

        $id = (int)$this->input->post('id');
        $payload = [
            'chapel_id'=>$chapel_id,
            'name'=>trim((string)$this->input->post('name',true)),
            'code'=>trim((string)$this->input->post('code',true)) ?: null,
            'description'=>$this->input->post('description'),
            'coverage_area'=>trim((string)$this->input->post('coverage_area',true)) ?: null,
            'meeting_schedule'=>trim((string)$this->input->post('meeting_schedule',true)) ?: null,
            'display_order'=>max(0,(int)$this->input->post('display_order')),
            'is_active'=>$this->input->post('is_active') ? 1 : 0,
        ];
        $saved = $this->Community_model->save_cluster($id,$payload);
        $this->json(['success'=>(bool)$saved,'message'=>$id?'GSK cluster updated.':'GSK cluster added.']);
    }

    public function cluster_delete($id)
    {
        $cluster = $this->Community_model->cluster((int)$id);
        if (!$cluster) return $this->json(['success'=>false,'message'=>'GSK cluster not found.'],404);

        $officials = $this->Community_model->officials('cluster', $cluster['chapel_id'], $cluster['id'], false);
        $deleted = $this->Community_model->delete_cluster((int)$id);

        if ($deleted) {
            foreach ($officials as $official) {
                if (!empty($official['photo'])) $this->delete_local_image($official['photo'],'uploads/officials/');
            }
        }

        $this->json([
            'success'=>(bool)$deleted,
            'message'=>$deleted ? 'GSK cluster removed.' : 'GSK cluster could not be removed.'
        ]);
    }

    public function official_get($id)
    {
        $item = $this->Community_model->official((int)$id);
        if (!$item) return $this->json(['success'=>false,'message'=>'Official not found.'],404);
        $item['photo_url'] = !empty($item['photo']) ? base_url($item['photo']) : null;
        $this->json(['success'=>true,'data'=>$item]);
    }

    public function official_store()
    {
        if (!$this->Community_model->schema_ready()) return $this->migration_required();

        $this->form_validation->set_rules('full_name','Full Name','required|max_length[180]');
        $this->form_validation->set_rules('position_title','Position','required|max_length[180]');
        if ($this->form_validation->run() === false) {
            return $this->json(['success'=>false,'message'=>strip_tags(validation_errors())]);
        }

        $scope = $this->input->post('scope_type',true);
        if (!in_array($scope,['parish','chapel','cluster'],true)) $scope='parish';

        $chapel_id = $scope === 'parish' ? null : (int)$this->input->post('chapel_id');
        $cluster_id = $scope === 'cluster' ? (int)$this->input->post('cluster_id') : null;

        if ($scope !== 'parish' && !$this->Community_model->chapel($chapel_id)) {
            return $this->json(['success'=>false,'message'=>'Select the chapel this official belongs to.']);
        }
        if ($scope === 'cluster') {
            $cluster = $this->Community_model->cluster($cluster_id);
            if (!$cluster || (int)$cluster['chapel_id'] !== $chapel_id) {
                return $this->json(['success'=>false,'message'=>'Select a valid GSK cluster under the chosen chapel.']);
            }
        }

        $id = (int)$this->input->post('id');
        $existing = $id ? $this->Community_model->official($id) : null;
        if ($id && !$existing) return $this->json(['success'=>false,'message'=>'Official not found.'],404);

        $term_start = $this->input->post('term_start',true) ?: null;
        $term_end = $this->input->post('term_end',true) ?: null;
        if ($term_start && $term_end && strtotime($term_end) < strtotime($term_start)) {
            return $this->json(['success'=>false,'message'=>'Term end cannot be earlier than term start.']);
        }

        $payload = [
            'scope_type'=>$scope,
            'chapel_id'=>$chapel_id,
            'cluster_id'=>$cluster_id,
            'full_name'=>trim((string)$this->input->post('full_name',true)),
            'position_title'=>trim((string)$this->input->post('position_title',true)),
            'committee_area'=>trim((string)$this->input->post('committee_area',true)) ?: null,
            'contact_number'=>trim((string)$this->input->post('contact_number',true)) ?: null,
            'email'=>trim((string)$this->input->post('email',true)) ?: null,
            'bio'=>trim((string)$this->input->post('bio',true)) ?: null,
            'term_start'=>$term_start,
            'term_end'=>$term_end,
            'display_order'=>max(0,(int)$this->input->post('display_order')),
            'is_active'=>$this->input->post('is_active') ? 1 : 0,
        ];

        $upload = $this->upload_image('photo','officials','official');
        if (!$upload['success']) return $this->json(['success'=>false,'message'=>$upload['message']]);
        if ($upload['path']) $payload['photo']=$upload['path'];

        $saved = $this->Community_model->save_official($id,$payload);
        if ($id && $upload['path'] && !empty($existing['photo'])) $this->delete_local_image($existing['photo'],'uploads/officials/');

        $this->json(['success'=>(bool)$saved,'message'=>$id?'Official updated.':'Official added.']);
    }

    public function official_delete($id)
    {
        $official = $this->Community_model->official((int)$id);
        if (!$official) return $this->json(['success'=>false,'message'=>'Official not found.'],404);

        if ($this->Community_model->delete_official((int)$id)) {
            if (!empty($official['photo'])) $this->delete_local_image($official['photo'],'uploads/officials/');
            return $this->json(['success'=>true,'message'=>'Official removed.']);
        }
        $this->json(['success'=>false,'message'=>'Official could not be removed.']);
    }

    private function migration_required()
    {
        return $this->json([
            'success'=>false,
            'message'=>'Run database/migrations/20260924_chapels_gsk_structure.sql in phpMyAdmin first.'
        ]);
    }

    private function upload_image($field,$folder,$prefix)
    {
        if (empty($_FILES[$field]['name'])) return ['success'=>true,'path'=>null];
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return ['success'=>false,'message'=>'Image upload did not complete successfully.'];
        if ((int)$_FILES[$field]['size'] > 5*1024*1024) return ['success'=>false,'message'=>'Image must be 5 MB or smaller.'];

        $ext = strtolower(pathinfo($_FILES[$field]['name'],PATHINFO_EXTENSION));
        if (!in_array($ext,['jpg','jpeg','png','webp'],true)) return ['success'=>false,'message'=>'Use a JPG, PNG or WebP image.'];
        if (!@getimagesize($_FILES[$field]['tmp_name'])) return ['success'=>false,'message'=>'The uploaded file is not a valid image.'];

        $dir = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($dir) && !@mkdir($dir,0755,true)) return ['success'=>false,'message'=>'The upload directory could not be created.'];

        $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        if (!move_uploaded_file($_FILES[$field]['tmp_name'],$dir.$name)) return ['success'=>false,'message'=>'The image could not be saved.'];

        return ['success'=>true,'path'=>'uploads/'.$folder.'/'.$name];
    }

    private function delete_local_image($path,$prefix)
    {
        if (strpos($path,$prefix)!==0) return;
        $full=FCPATH.$path;
        if (is_file($full)) @unlink($full);
    }
}

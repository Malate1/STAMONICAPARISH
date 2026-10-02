<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate_model extends CI_Model
{
    protected $table = 'certificate_requests';

    public function generate_code()
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = 'SMC-CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            if (!$this->db->where('request_code', $code)->count_all_results($this->table)) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique certificate request code.');
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    private $last_transition_error = '';

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function allowed_status_transitions(array $request)
    {
        switch ($request['status'] ?? 'submitted') {
            case 'submitted':
                return ['searching_record', 'cancelled'];

            case 'searching_record':
                return ['record_found', 'no_record_found', 'cancelled'];

            case 'record_found':
                return [(float)($request['fee_amount'] ?? 0) > 0 ? 'awaiting_payment' : 'preparing', 'cancelled'];

            case 'no_record_found':
                if (($request['certificate_type'] ?? '') === 'no_record') {
                    return [
                        (float)($request['fee_amount'] ?? 0) > 0 ? 'awaiting_payment' : 'preparing',
                        'searching_record',
                        'cancelled'
                    ];
                }
                return ['searching_record', 'cancelled'];

            case 'awaiting_payment':
                return ['payment_verification', 'cancelled'];

            case 'payment_verification':
                return ['preparing', 'awaiting_payment', 'cancelled'];

            case 'preparing':
                return ['ready_for_release', 'cancelled'];

            case 'ready_for_release':
                return ['released'];

            case 'released':
            case 'cancelled':
            default:
                return [];
        }
    }

    public function transition_error()
    {
        return $this->last_transition_error;
    }

    public function change_status($id, $new_status, $processed_by = null)
    {
        $this->last_transition_error = '';
        $request = $this->get((int)$id);

        if (!$request) {
            $this->last_transition_error = 'Certificate request not found.';
            return false;
        }

        if ($new_status === $request['status']) {
            return true;
        }

        $allowed = $this->allowed_status_transitions($request);
        if (!in_array($new_status, $allowed, true)) {
            $this->last_transition_error = empty($allowed)
                ? 'This certificate request is already in a final state.'
                : 'The request cannot move from ' . status_label($request['status'])
                    . ' directly to ' . status_label($new_status) . '.';
            return false;
        }

        $is_no_record_certificate = ($request['certificate_type'] ?? '') === 'no_record';

        if (!$is_no_record_certificate
            && in_array($new_status, ['record_found','preparing','ready_for_release','released'], true)
            && empty($request['record_id'])) {
            $this->last_transition_error = 'A verified sacramental record must be linked first.';
            return false;
        }

        if (!$is_no_record_certificate
            && in_array($new_status, ['preparing','ready_for_release','released'], true)
            && $this->db->field_exists('record_status', 'sacramental_records')) {
            $record = $this->db->get_where('sacramental_records', ['id' => (int)$request['record_id']])->row_array();
            if (!$record || ($record['record_status'] ?? 'verified') !== 'verified') {
                $this->last_transition_error = 'The linked sacramental record must be Verified first.';
                return false;
            }
        }

        if (in_array($new_status, ['preparing','ready_for_release','released'], true)
            && (float)($request['fee_amount'] ?? 0) > 0) {
            $payment = $this->db->where('payable_type','certificate_request')
                ->where('payable_id',(int)$id)
                ->get('payments')->row_array();

            if (!$payment || $payment['status'] !== 'payment_verified') {
                $this->last_transition_error = 'The certificate payment must be verified first.';
                return false;
            }
        }

        $payload = ['status' => $new_status];
        if ($processed_by !== null) $payload['processed_by'] = (int)$processed_by;
        if ($new_status === 'released') $payload['released_at'] = date('Y-m-d H:i:s');

        if (!$this->db->where('id',(int)$id)->update($this->table,$payload)) {
            $this->last_transition_error = 'The certificate status could not be saved.';
            return false;
        }

        return true;
    }

    public function link_verified_record_and_advance($id, $record_id, $processed_by = null)
    {
        $this->last_transition_error = '';
        $request = $this->get((int)$id);
        if (!$request) {
            $this->last_transition_error = 'Certificate request not found.';
            return false;
        }

        if (!in_array($request['status'], ['submitted','searching_record','record_found'], true)) {
            $this->last_transition_error = 'This certificate request is no longer in record-search processing.';
            return false;
        }

        if (($request['certificate_type'] ?? '') === 'no_record') {
            $this->last_transition_error = 'A Certificate of No Record cannot be linked to an existing sacramental entry. If a matching record was found, do not issue a No Record certificate; process the appropriate certificate request instead.';
            return false;
        }

        $record = $this->db->get_where('sacramental_records',['id'=>(int)$record_id])->row_array();
        if (!$record) {
            $this->last_transition_error = 'Sacramental record not found.';
            return false;
        }

        if ($this->db->field_exists('record_status','sacramental_records')
            && ($record['record_status'] ?? 'verified') !== 'verified') {
            $this->last_transition_error = 'This sacramental record is still Draft and must be verified first.';
            return false;
        }

        $next_status = (float)$request['fee_amount'] > 0 ? 'awaiting_payment' : 'preparing';

        $this->db->trans_start();
        $this->db->where('id',(int)$id)->update($this->table,[
            'record_id' => (int)$record_id,
            'status' => 'record_found',
            'processed_by' => $processed_by !== null ? (int)$processed_by : $request['processed_by'],
        ]);
        $this->db->where('id',(int)$id)->update($this->table,[
            'status' => $next_status,
            'processed_by' => $processed_by !== null ? (int)$processed_by : $request['processed_by'],
        ]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->last_transition_error = 'The verified record could not be linked to this certificate request.';
            return false;
        }

        return $next_status;
    }

    public function get($id)
    {
        return $this->db->select('certificate_requests.*, users.first_name, users.last_name, users.email,
                sacramental_records.full_name as record_name, sacramental_records.sacrament_date, sacramental_records.registry_book, sacramental_records.registry_page')
            ->from($this->table)
            ->join('users', 'users.id = certificate_requests.user_id')
            ->join('sacramental_records', 'sacramental_records.id = certificate_requests.record_id', 'left')
            ->where('certificate_requests.id', $id)
            ->get()->row_array();
    }

    public function get_by_token($token)
    {
        return $this->db->get_where($this->table, ['qr_code_token' => $token])->row_array();
    }

    public function printable_record($certificate_id)
    {
        $request = $this->get((int)$certificate_id);
        if (!$request || empty($request['record_id'])) return null;

        return $this->db->get_where('sacramental_records', [
            'id' => (int)$request['record_id']
        ])->row_array();
    }

    public function for_user($user_id)
    {
        return $this->db->where('user_id', $user_id)->order_by('created_at', 'desc')->get($this->table)->result_array();
    }

    public function search_records($type, $keyword)
    {
        $this->db->where('record_type', $type);

        if ($this->db->field_exists('record_status', 'sacramental_records')) {
            $this->db->where('record_status', 'verified');
        }

        return $this->db
            ->group_start()
                ->like('full_name', $keyword)
                ->or_like('father_name', $keyword)
                ->or_like('mother_name', $keyword)
            ->group_end()
            ->limit(20)
            ->get('sacramental_records')->result_array();
    }

    public function datatable_query($request, $scope = [], $count_only = false)
    {
        $this->db->select('certificate_requests.*, users.first_name, users.last_name, users.email')
            ->from($this->table)
            ->join('users', 'users.id = certificate_requests.user_id');

        if (!empty($scope['user_id'])) {
            $this->db->where('certificate_requests.user_id', $scope['user_id']);
        }
        if (!empty($request['status'])) {
            $this->db->where('certificate_requests.status', $request['status']);
        }
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('certificate_requests.request_code', $kw)
                ->or_like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->group_end();
        }

        if ($count_only) return $this->db->count_all_results();

        $this->db->order_by('certificate_requests.id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }
}

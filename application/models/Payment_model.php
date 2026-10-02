<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    protected $table = 'payments';

    public function generate_code()
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = 'SMC-PAY-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            if (!$this->db->where('payment_code', $code)->count_all_results($this->table)) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique payment code.');
    }

    public function generate_receipt_no($payment_id)
    {
        return sprintf('OR-%s-%07d', date('Y'), (int) $payment_id);
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function get($id)
    {
        return $this->db->select('payments.*, users.first_name, users.last_name, users.email')
            ->from($this->table)
            ->join('users', 'users.id = payments.user_id')
            ->where('payments.id', $id)
            ->get()->row_array();
    }

    public function for_payable($type, $id)
    {
        return $this->db->where('payable_type', $type)->where('payable_id', $id)
            ->order_by('created_at', 'desc')->get($this->table)->row_array();
    }

    public function for_user($user_id)
    {
        return $this->db->where('user_id', $user_id)->order_by('created_at', 'desc')->get($this->table)->result_array();
    }

    public function datatable_query($request, $count_only = false)
    {
        $this->db->select('payments.*, users.first_name, users.last_name')
            ->from($this->table)
            ->join('users', 'users.id = payments.user_id');

        if (!empty($request['status'])) {
            $this->db->where('payments.status', $request['status']);
        }
        if (!empty($request['search']['value'])) {
            $kw = $request['search']['value'];
            $this->db->group_start()
                ->like('payments.payment_code', $kw)
                ->or_like('payments.gcash_reference_no', $kw)
                ->or_like('users.first_name', $kw)
                ->or_like('users.last_name', $kw)
                ->group_end();
        }

        if ($count_only) return $this->db->count_all_results();

        $this->db->order_by('payments.id', 'desc');
        if (isset($request['start'], $request['length']) && (int) $request['length'] !== -1) {
            $this->db->limit((int) $request['length'], (int) $request['start']);
        }
        return $this->db->get()->result_array();
    }

    public function total_verified_between($from, $to)
    {
        return $this->db->select_sum('amount')
            ->where('status', 'payment_verified')
            ->where('verified_at >=', $from)
            ->where('verified_at <=', $to)
            ->get($this->table)->row_array()['amount'] ?? 0;
    }
}

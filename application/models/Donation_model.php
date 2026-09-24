<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Donation_model extends CI_Model
{
    protected $table = 'donations';

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get($id)
    {
        return $this->db->select('donations.*, donation_campaigns.title AS campaign_title, donation_campaigns.slug AS campaign_slug')
            ->from($this->table)
            ->join('donation_campaigns', 'donation_campaigns.id = donations.campaign_id', 'left')
            ->where('donations.id', (int) $id)
            ->get()->row_array();
    }

    public function for_user($user_id)
    {
        return $this->db->select('donations.*, donation_campaigns.title AS campaign_title, payments.status AS payment_status, payments.receipt_no')
            ->from($this->table)
            ->join('donation_campaigns', 'donation_campaigns.id = donations.campaign_id', 'left')
            ->join('payments', "payments.payable_type = 'donation' AND payments.payable_id = donations.id", 'left', false)
            ->where('donations.user_id', (int) $user_id)
            ->order_by('donations.created_at', 'desc')
            ->get()->result_array();
    }
}

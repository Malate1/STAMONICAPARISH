<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model
{
    protected $table = 'notifications';

    public function push($user_id, $title, $message, $link = null)
    {
        $this->db->insert($this->table, [
            'user_id'    => $user_id,
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function for_user($user_id, $limit = 10)
    {
        return $this->db->where('user_id', $user_id)->order_by('created_at', 'desc')->limit($limit)->get($this->table)->result_array();
    }

    public function unread_count($user_id)
    {
        return $this->db->where('user_id', $user_id)->where('is_read', 0)->count_all_results($this->table);
    }

    public function mark_all_read($user_id)
    {
        return $this->db->where('user_id', $user_id)->update($this->table, ['is_read' => 1]);
    }

    public function mark_read($id, $user_id)
    {
        return $this->db->where('id', $id)->where('user_id', $user_id)->update($this->table, ['is_read' => 1]);
    }
}

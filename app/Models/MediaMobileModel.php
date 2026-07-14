<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaMobileModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getMediaFiles($limit = 10, $offset = 0,)
    {
        $builder = $this->db->table('media m')
            ->select('m.id, m.file_name,m.file_path,m.is_downloadable,m.media_type, m.storage,m.user_id')
            ->where('m.media_type', 'file')
            ->orderBy('m.id', 'DESC');

        $total = $builder->countAllResults(false);

        $media_files = $builder
            ->limit($limit, $offset)
            ->get()
            ->getResult();

        foreach ($media_files as $file) {
            $file->id = (int)$file->id;
                        $file->is_downloadable = (int)$file->is_downloadable;

        }
        return [
            'data' => $media_files,

            'meta' => [
                'total'       => $total,
                'limit'       => $limit,
                'offset'      => $offset,
                'total_pages' => ceil($total / $limit),
                'current_page' => floor($offset / $limit) + 1
            ]
        ];
    }

    public function singleFile($id)
    {
        $builder = $this->db->table('media m')
            ->select('m.id, m.file_name,m.file_path,m.is_downloadable,m.media_type, m.storage,m.user_id')
            ->where('m.id', $id);

        $media_file = $builder->get()->getRow();
        $media_file->id = (int)$media_file->id;
         $media_file->is_downloadable = (int)$media_file->is_downloadable;
        return $media_file;
    }
}

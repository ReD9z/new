<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ManagerTask extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->clients ? $this->clients->users->name : null,
            'manager_id' => $this->manager_id,
            'managers' => $this->managers ? $this->managers->users->name : null,
            'task_date_completion' => $this->task_date_completion ? date("d.m.Y", strtotime($this->task_date_completion)) : null,
            'comment' => $this->comment,
            'email' => $this->clients ? $this->clients->users->email : null,
            'phone' => $this->clients ? $this->clients->users->phone : null,
            'status' => $this->status,
            'task_date_ended' => $this->TaskEndDate($this->client_id),
            'result' => $this->result,
            'created_at' => $this->clients ? date("d.m.Y", strtotime($this->clients->created_at)) : null,
            'statusName' => $this->status == '1' ? 'В работе' : 'Завершена'
        ];
    }
}
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class GanttTask extends Model
{
    protected $table = 'r_gantt_tasks';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $appends = ["open"];
 
    public function getOpenAttribute(){
        return true;
    }
}
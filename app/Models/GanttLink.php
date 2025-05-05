<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class GanttLink extends Model
{
    protected $table = 'r_gantt_links';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
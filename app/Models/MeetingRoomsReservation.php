<?php
  
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class MeetingRoomsReservation extends Model
{
    use HasFactory;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'memo',
        'meeting_rooms_id',
        'reservation_start',
        'reservation_end'
    ];

    public function MeetingRoom(){
        return $this->belongsTo(MeetingRoom::class, 'meeting_rooms_id');
    }

    public function Participants(){
        return $this->hasMany(MeetingRoomsReservationParticipant::class, 'meeting_rooms_reservations_id');
    }
}
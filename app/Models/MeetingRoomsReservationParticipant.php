<?php
  
namespace App\Models;
  
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class MeetingRoomsReservationParticipant extends Model
{
    use HasFactory;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_rooms_reservations_id',
        'users_id'
    ];

    public function User(){
        return $this->belongsTo(User::class, 'users_id');
    }

    public function MeetingRoomReservation(){
        return $this->belongsTo(MeetingRoomsReservation::class, 'meeting_rooms_reservations_id');
    }
}
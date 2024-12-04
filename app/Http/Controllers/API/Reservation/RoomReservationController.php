<?php
   
namespace App\Http\Controllers\API\Reservation;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\MeetingRoomsReservation;
use Validator;
use App\Http\Resources\RoomReservationResource;
use App\Models\MeetingRoomsReservationParticipant;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RoomReservationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $meetingroomreservation = MeetingRoomsReservation::where('created_by', $request->user()->id)
                                ->orWhereHas('Participants', function($q) use($request){
                                    return $q->where('users_id', $request->user()->id);
                                })->get();
    
        return $this->sendResponse(RoomReservationResource::collection($meetingroomreservation), 'MeetingRoomsReservations retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'meeting_rooms_id' => 'required',
            'reservation_start' => 'required|date_format:Y-m-d H:i:s',
            'reservation_end' => 'required|date_format:Y-m-d H:i:s|after:reservation_start',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
        
        $reservation = MeetingRoomsReservation::where('reservation_start', '>=', $input['reservation_start'])
                        ->where('reservation_end', '<=', $input['reservation_end'])
                        ->where('meeting_rooms_id', $input['meeting_rooms_id'])->get();

        if(!$reservation->isEmpty()){
            return $this->sendError('The room is already reserved for those date and time.', [], 400);     
        }

        DB::beginTransaction();
        try {
            $meetingroomreservation = MeetingRoomsReservation::create($input);
            $meetingroomreservation->created_by = $request->user()->id;
            $meetingroomreservation->save();

            $data = [
                'users_id' => $request->user()->id,
                'meeting_rooms_reservations_id' => $meetingroomreservation->id,
            ];

            $ReservationParticipant = MeetingRoomsReservationParticipant::create($data);

            if($request->has('participants')){  
                foreach($input['participants'] as $participant){
                    $user = User::find($participant);
                    if(!$user){
                        return $this->sendError('User not found.', [], 404); 
                    }

                    $meetingparticipant = MeetingRoomsReservationParticipant::where('users_id', $participant)
                    ->where('meeting_rooms_reservations_id', $meetingroomreservation->id)
                    ->first();

                    if(!$meetingparticipant){
                        $data = [
                            'users_id' => $participant,
                            'meeting_rooms_reservations_id' => $meetingroomreservation->id,
                        ];
                        $ReservationParticipant = MeetingRoomsReservationParticipant::create($data);
                    }
                }
            }

            $meetingroomreservation = MeetingRoomsReservation::with('Participants.User')
                                    ->where('id', $meetingroomreservation->id)->first();
            DB::commit();
            return $this->sendResponse(new RoomReservationResource($meetingroomreservation), 'MeetingRoomsReservation created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    } 

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $meetingroomreservation = MeetingRoomsReservation::find($id);
  
        if (is_null($meetingroomreservation)) {
            return $this->sendError('MeetingRoomsReservation not found.');
        }
   
        return $this->sendResponse(new RoomReservationResource($meetingroomreservation), 'MeetingRoomsReservation retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->all();
        $meetingroomreservation = MeetingRoomsReservation::find($id);
        
        if(!$meetingroomreservation){
            return $this->sendError('MeetingRoomsReservation not found.', [], 404); 
        }

        if($meetingroomreservation->created_by != $request->user()->id){
            return $this->sendError('You dont have access to edit this meeting.', [], 401); 
        }

        DB::beginTransaction();
        try {
            if($request->has('participants')){  
                foreach($input['participants'] as $participant){
                    $user = User::find($participant);
                    if(!$user){
                        return $this->sendError('User not found.', [], 404); 
                    }

                    $meetingparticipant = MeetingRoomsReservationParticipant::where('users_id', $participant)
                                        ->where('meeting_rooms_reservations_id', $meetingroomreservation->id)
                                        ->first();

                    if(!$meetingparticipant){
                        $data = [
                            'users_id' => $participant,
                            'meeting_rooms_reservations_id' => $meetingroomreservation->id,
                        ];
                        $ReservationParticipant = MeetingRoomsReservationParticipant::create($data);
                    }
                }
            }
   
            if($request->has('memo')){  
                $meetingroomreservation->memo = $input['memo'];
                $meetingroomreservation->save();
            }
            DB::commit();
            return $this->sendResponse(new RoomReservationResource($meetingroomreservation), 'MeetingRoomsReservation updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MeetingRoomsReservation $meetingroomreservation): JsonResponse
    {
        $meetingroomreservation->delete();
   
        return $this->sendResponse([], 'MeetingRoomsReservation deleted successfully.');
    }
}
<?php
   
namespace App\Http\Controllers\API\Masterdata;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\MeetingRoom;
use Validator;
use App\Http\Resources\MeetingRoomResource;
use App\Models\MeetingRoomsReservation;
use Illuminate\Http\JsonResponse;
   
class MeetingRoomController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $meetingrooms = MeetingRoom::all();
    
        return $this->sendResponse(MeetingRoomResource::collection($meetingrooms), 'MeetingRooms retrieved successfully.');
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
            'name' => 'required',
            'capacity' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
   
        $meetingroom = MeetingRoom::create($input);
   
        return $this->sendResponse(new MeetingRoomResource($meetingroom), 'MeetingRoom created successfully.');
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $meetingroom = MeetingRoom::find($id);
  
        if (is_null($meetingroom)) {
            return $this->sendError('MeetingRoom not found.');
        }
   
        return $this->sendResponse(new MeetingRoomResource($meetingroom), 'MeetingRoom retrieved successfully.');
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
        $meetingroom = MeetingRoom::find($id);
        
        if(!$meetingroom){
            return $this->sendError('MeetingRoom not found.', [], 404); 
        }

        $validator = Validator::make($input, [  
            'name' => 'required',
            'capacity' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(), 400);       
        }
   
        $meetingroom->name = $input['name'];
        $meetingroom->capacity = $input['capacity'];
        $meetingroom->save();
   
        return $this->sendResponse(new MeetingRoomResource($meetingroom), 'MeetingRoom updated successfully.');
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MeetingRoom $meetingroom): JsonResponse
    {
        $meetingroom->delete();
   
        return $this->sendResponse([], 'MeetingRoom deleted successfully.');
    }
}
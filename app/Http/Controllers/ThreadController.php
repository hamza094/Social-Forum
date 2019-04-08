<?php

namespace App\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Channel;
use App\User;
use App\Trending;
use App\Rules\Recaptcha;


class ThreadController extends Controller
{
    public function __construct(){
        $this->middleware('auth')->except(['index','show']);
        $this->middleware('verified')->except(['index','show']);

    }
    /**
     * Display a listing of the resource.
     *
     *param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel,Trending $trending)
    {
        if($channel->exists){
            $threads=$channel->threads()->latest();
        }else{
           $threads=Thread::latest();     
        }
        
        if($username=request('by')){
            $user=\App\User::where('name',$username)->firstOrFail();
            $threads->where('user_id',$user->id);
        }elseif(request('popular')){
            $threads->getQuery()->orders=[];
             $threads->orderBy('replies_count','desc');
        }elseif(request('unanswered')){
           $threads->getQuery()->orders=[];
             $threads->where('replies_count',0);
        }
       
        $threads=$threads->paginate(25);
        
        return view('threads.index',[
            'threads'=>$threads,
            'trending'=>$trending->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
     return view('threads.create');   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Recaptcha $recaptcha)
    {
        
        
        $this->validate($request,[
            'title'=>'required|spamfree',
            'body'=>'required|spamfree',
            'channel_id'=>'required|exists:channels,id',
            'g-recaptcha-response'=>['required',$recaptcha]
        ]);
        
        $thread=Thread::create([
            'user_id'=>auth()->id(),
            'title'=>request('title'),
            'body'=>request('body'),
            'channel_id'=>request('channel_id')
            
        ]);
        if(request()->wantsJson()){
            return response($thread,201); 
        }
        return redirect($thread->path())
            ->with('flash','Your thread has been published');
    }

    /**
     * Display the specified resource.
     *
     * @param $channelId
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function show($channelId,Thread $thread,Trending $trending)
    {
        $key=sprintf("users.%s.visits.%s",auth()->id(),$thread->id);
        cache()->forever($key,Carbon::now());
        
        $trending->push($thread);
        
        $thread->increment('visits');
        
         return view('threads.show',compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function update($channel,Request $request, Thread $thread)
    {
        $this->authorize('update',$thread);
        $this->validate($request,[
            'title'=>'required|spamfree',
            'body'=>'required|spamfree',
           ]);
        
         $thread->update(request(['body','title']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Thread  $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy($channel,Thread $thread)
    {
        $this->authorize('update',$thread);
        $thread->delete();
        if(request()->wantsJson()){
            return response([],204);
        }
         return redirect('/threads');  
      
    }
}

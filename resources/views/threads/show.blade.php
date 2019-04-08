@extends('layouts.app')

@section('header')
    <link rel="stylesheet" href="/css/vendor/jquery.atwho.css">
@endsection

@section('content')
<thread-view :thread="{{$thread}}" inline-template>
<div class="container">
    <div class="row ">
        <div class="col-md-8" v-cloak>
        @include('threads._discussion')
          <h3 class="text-center mt-4">Thread Replies</h3>
          @if(auth()->check())
          @if(auth()->user()->email_verified_at!==null)
           <replies
          @added="repliesCount++"
           @removed="repliesCount--">
           </replies>
           @endif
           @else
           @include('threads.singlereply')
            <p>Please <a href="/login">Login</a> to access all features</p>
           @endif
                
            <br>
          </div>
        <div class="col-md-4">
                  <div class="card">
                <div class="card-body">
                    This Thread was publised {{$thread->created_at->diffforhumans()}} by <a href="{{route('profile',$thread->creator)}}">{{$thread->creator->name}}</a>, and currently has <span v-text="repliesCount"></span> {{str_plural('comment',$thread->replies_count)}}.
                    <br>
                    @if(auth()->check())
                    @if(auth()->user()->email_verified_at!==null)
                    <subscribe :active="{{json_encode($thread->isSubscribedTo)}}" v-if="!locked"></subscribe>
                    <button class="btn btn-primary" v-if="authorize('isAdmin')" @click="toogleLock" v-text="locked ? 'Unlock' : 'lock' ">Lock</button>
                    @endif
                    @endif
                  </div>
            </div>
        </div>
    </div>
</div>
</thread-view>
@endsection
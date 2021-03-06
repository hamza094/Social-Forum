@extends('layouts.app')

@section('content')
<div class="container">
    <ais-index app-id="{{ config('scout.algolia.id') }}"
                api-key="{{ env('ALGOLIA_SEARCH') }}"
                index-name="threads"
                query="{{request('q')}}"
                >
    <div class="row">
      
        <div class="col-md-8">
         <ais-results>
        <template scope="{result}">
       <li class="list-group list-group-flush">
       <a :href="result.path" class="list-group-item">
        <ais-highlight :result="result" attribute-name="title"></ais-highlight>
       </a>
       </li>
        </template>
      </ais-results>
        </div>
        <div class="col-md-4">
         <div class="card">
             <div class="card-header">
                  Search Threads
             </div>
             <div class="card-body">
                 <ais-search-box>
                       <ais-input placeholder="Find a thread..." :autofocus="true" class="form-control search_input"></ais-input>
                 </ais-search-box>
                 
             </div>
         </div>
            <br>
             <div class="card">
             <div class="card-header">
                  Filter By Channel
             </div>
             <div class="card-body">
             
            <ais-refinement-list attribute-name="channel.name"></ais-refinement-list>
                
          </div>
         </div>
           <hr>
        </div>
        
    </div>
    </ais-index>
</div>
@endsection
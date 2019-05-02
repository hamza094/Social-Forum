<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Channel;

class ChannelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    
     /** @test */
    public function a_channel_consist_a_threads(){
        $channel=create('App\Channel');
        $thread=create('App\Thread',['channel_id'=>$channel->id]);
        $this->assertTrue($channel->threads->contains($thread));
    } 
    
    /** @test */ 
    public function a_channel_can_be_archived(){
        $channel=create('App\Channel');
        $this->assertFalse($channel->archived);
        $channel->archive();
        $this->assertTrue($channel->archived);
    }
    
    /** @test */
    public function archived_channels_excluded_by_default(){
        create('App\Channel');
        create('App\Channel',['archived'=>'true']);
        $this->assertEquals(1,Channel::count());
    }
}

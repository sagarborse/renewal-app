<?php

namespace app;

use Illuminate\Database\Eloquent\Model;

class Abtest extends Model
{

    protected $table = 'abtest';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'domain', 'path', 'testUrl', 'status', 'visitorCount','shownCount', 'targetPercent'];


}

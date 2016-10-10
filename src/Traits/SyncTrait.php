<?php namespace BadChoice\Grog\Traits;

trait SyncTrait{

    /*
    |--------------------------------------------------------------------------
    | SYNC FILTER
    |--------------------------------------------------------------------------
    |
    | This function will be used to filter the models to sync
    |
    */
    public static function syncFilter($query){
        return $query;
    }


    public static function shouldSync($fromDate){
        if($fromDate == '') return '1';
        $instance    = new static;
        $shouldSync  = static::syncFilter($instance->newQuery()->withTrashed()->where('updated_at', '>', $fromDate)->select('id'))->first();
        return ($shouldSync != null) ? "1" : "0";
    }

    public static function sync($fromDate = ''){
        $instance    = new static;
        $newQuery    = static::syncFilter($instance->newQuery()->where(function($query) use (&$fromDate){
            $query->where('created_at','>',$fromDate)->orWhereNull('created_at');
        }));
        $updateQuery = static::syncFilter($instance->newQuery()->where('updated_at', '>', $fromDate)->where('created_at','<', $fromDate ));
        $deleteQuery = static::syncFilter($instance->newQuery()->onlyTrashed()->where('deleted_at', '>', $fromDate));

        if($fromDate == ''){
            return array(
                'new'       => $newQuery->get()->toArray(),
                'updated'   => null,
                'deleted'   => null,
            );
        }

        return array(
            'new'       => $newQuery    ->get()->toArray(),
            'updated'   => $updateQuery ->get()->toArray(),
            'deleted'   => $deleteQuery ->get()->toArray(),
        );
    }
}
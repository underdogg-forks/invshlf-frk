<?php

namespace App\Models;

use App\Enums\FileDiskType;
use App\Models\BaseModel;
use App\Models\Concerns\BelongsToFranchise;
use Carbon\Carbon;

class FileDisk extends BaseModel
{
    use BelongsToFranchise;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'set_as_default' => 'boolean',
            'type' => FileDiskType::class,
        ];
    }

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public function setConfig()
    {
        $driver = $this->driver;

        $credentials = collect(json_decode($this['credentials']));

        self::setFilesystem($credentials, $driver);
    }

    public function setAsDefault()
    {
        return $this->set_as_default;
    }

    public static function setFilesystem($credentials, $driver)
    {
        $prefix = env('DYNAMIC_DISK_PREFIX', 'temp_');

        config(['filesystems.default' => $prefix.$driver]);

        $disks = config('filesystems.disks.'.$driver);

        foreach ($disks as $key => $value) {
            if ($credentials->has($key)) {
                $disks[$key] = $credentials[$key];
            }
        }

        config(['filesystems.disks.'.$prefix.$driver => $disks]);
    }

    public static function validateCredentials($credentials, $disk)
    {
        $exists = false;

        self::setFilesystem(collect($credentials), $disk);

        $prefix = env('DYNAMIC_DISK_PREFIX', 'temp_');

        try {
            $root = '';
            if ($disk == 'dropbox') {
                $root = $credentials['root'].'/';
            }
            \Storage::disk($prefix.$disk)->put($root.'invoiceshelf_temp.text', 'Check Credentials');

            if (\Storage::disk($prefix.$disk)->exists($root.'invoiceshelf_temp.text')) {
                $exists = true;
                \Storage::disk($prefix.$disk)->delete($root.'invoiceshelf_temp.text');
            }
        } catch (\Exception $e) {
            $exists = false;
        }

        return $exists;
    }

    public function isSystem()
    {
        return $this->type === FileDiskType::System;
    }

    public function isRemote()
    {
        return $this->type === FileDiskType::Remote;
    }

    #endregion
    #region Relationships
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Accessors
    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Mutators
    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = json_encode($value);
    }

    #endregion
    #region Scopes
    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApplyFilters($query, array $filters)
    {
        $filters = collect($filters);
        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('from_date') && $filters->get('to_date')) {
            $start = Carbon::createFromFormat('Y-m-d', $filters->get('from_date'));
            $end = Carbon::createFromFormat('Y-m-d', $filters->get('to_date'));
            $query->fileDisksBetween($start, $end);
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'sequence_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopeFileDisksBetween($query, $start, $end)
    {
        return $query->whereBetween(
            'file_disks.created_at',
            [$start->format('Y-m-d'), $end->format('Y-m-d')]
        );
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            return $query->get();
        }

        return $query->paginate($limit);
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereSearch($query, $search)
    {
        foreach (explode(' ', $search) as $term) {
            $query->where('name', 'LIKE', '%'.$term.'%')
                ->orWhere('driver', 'LIKE', '%'.$term.'%');
        }
    }

    #endregion
    #region Factory
    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */

    public static function createDisk($request)
    {
        if ($request->set_as_default) {
            self::updateDefaultDisks();
        }

        $disk = self::create([
            'credentials' => $request->credentials,
            'name' => $request->name,
            'driver' => $request->driver,
            'set_as_default' => $request->set_as_default,
            'company_id' => $request->header('company'),
        ]);

        return $disk;
    }

    public static function updateDefaultDisks()
    {
        $disks = self::get();

        foreach ($disks as $disk) {
            $disk->set_as_default = false;
            $disk->save();
        }

        return true;
    }

    public function updateDisk($request)
    {
        $data = [
            'credentials' => $request->credentials,
            'name' => $request->name,
            'driver' => $request->driver,
        ];

        if (! $this->setAsDefault()) {
            if ($request->set_as_default) {
                self::updateDefaultDisks();
            }

            $data['set_as_default'] = $request->set_as_default;
        }

        $this->update($data);

        return $this;
    }

    public function setAsDefaultDisk()
    {
        self::updateDefaultDisks();

        $this->set_as_default = true;
        $this->save();

        return $this;
    }

    #endregion
}

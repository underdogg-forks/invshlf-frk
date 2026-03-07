<?php

namespace App\Models;

use App\Enums\TaxTypeType;
use App\Models\BaseModel;
use App\Models\Concerns\BelongsToFranchise;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxType extends BaseModel
{
    use BelongsToFranchise;

    public const TYPE_GENERAL = TaxTypeType::General->value;

    public const TYPE_MODULE = TaxTypeType::Module->value;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'float',
            'fixed_amount' => 'integer',
            'compound_tax' => 'boolean',
        ];
    }

    #region Static Methods
    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    #endregion
    #region Relationships
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

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

        if ($filters->get('tax_type_id')) {
            $query->whereTaxType($filters->get('tax_type_id'));
        }

        if ($filters->get('company_id')) {
            $query->whereCompany($filters->get('company_id'));
        }

        if ($filters->get('search')) {
            $query->whereSearch($filters->get('search'));
        }

        if ($filters->get('orderByField') || $filters->get('orderBy')) {
            $field = $filters->get('orderByField') ? $filters->get('orderByField') : 'payment_number';
            $orderBy = $filters->get('orderBy') ? $filters->get('orderBy') : 'asc';
            $query->whereOrder($field, $orderBy);
        }
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            return $query->get();
        }

        return $query->paginate($limit);
    }

    public function scopeWhereCompany($query)
    {
        $query->where('company_id', request()->header('company'));
    }

    public function scopeWhereOrder($query, $orderByField, $orderBy)
    {
        $query->orderBy($orderByField, $orderBy);
    }

    public function scopeWhereSearch($query, $search)
    {
        $query->where('name', 'LIKE', '%'.$search.'%');
    }

    public function scopeWhereTaxType($query, $tax_type_id)
    {
        $query->orWhere('id', $tax_type_id);
    }

    #endregion
    #region Factory
    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */

    #endregion
}

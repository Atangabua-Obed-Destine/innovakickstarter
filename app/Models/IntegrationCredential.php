<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A key or setting for an outside service, saved from the Integrations page.
 * The value is encrypted at rest with APP_KEY.
 *
 * @property int $id
 * @property string $key
 * @property string $value Decrypted value
 * @property int|null $updated_by
 */
class IntegrationCredential extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    protected $hidden = ['value'];

    protected function casts(): array
    {
        return ['value' => 'encrypted'];
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

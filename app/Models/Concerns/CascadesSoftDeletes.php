<?php

namespace App\Models\Concerns;

/**
 * Carries a soft delete down to the children a hard delete used to take with it.
 *
 * waste_entries.generator_id and inspections.generator_id are ON DELETE CASCADE,
 * so while generators were hard-deleted the database removed their rows too.
 * Soft deleting fires no such cascade: without this trait a generator would
 * disappear from its own list while its entries stayed live, showing a dash
 * where the generator's name belongs and still counting toward every total.
 *
 * Models opting in name the relations to follow:
 *
 *     protected static function cascadeSoftDeletes(): array
 *     {
 *         return ['wasteEntries', 'inspections'];
 *     }
 *
 * Restoring the parent brings the same children back, which is what makes
 * "restore" mean something in the archive.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait CascadesSoftDeletes
{
    public static function bootCascadesSoftDeletes(): void
    {
        static::deleting(function ($model) {
            // On a force delete the database's own ON DELETE CASCADE is the
            // right mechanism; repeating it here would be a second truth.
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            foreach (static::cascadeSoftDeletes() as $relation) {
                // Per row rather than a mass builder delete: a mass delete on a
                // soft-deleting relation is an UPDATE that fires no model
                // events, so InvalidatesDataCache would not flush and a nested
                // cascade would not run at all.
                $model->{$relation}()->get()->each->delete();
            }
        });

        static::restoring(function ($model) {
            foreach (static::cascadeSoftDeletes() as $relation) {
                // Restores every currently-archived child, including any deleted
                // on their own before the parent was. At this data's scale that
                // is the right trade for not tracking each cascade set.
                $model->{$relation}()->onlyTrashed()->get()->each->restore();
            }
        });
    }

    /**
     * Relations whose rows follow this model into and out of the archive.
     *
     * @return list<string>
     */
    protected static function cascadeSoftDeletes(): array
    {
        return [];
    }
}

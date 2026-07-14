<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Loop through all activities that have resources
        $activities = DB::table('track_curriculum_activities')
            ->whereNotNull('resources')
            ->get();

        foreach ($activities as $activity) {
            $resources = json_decode($activity->resources, true);
            if (!is_array($resources)) continue;

            $newResources = [];
            foreach ($resources as $resource) {
                // If it's already an array/object with a type, it's likely already migrated
                if (is_array($resource) && isset($resource['type'])) {
                    $newResources[] = $resource;
                    continue;
                }

                // If it's a string, convert it to the new object format
                if (is_string($resource)) {
                    if (str_contains($resource, 'youtube.com') || str_contains($resource, 'youtu.be')) {
                        $newResources[] = [
                            'title' => 'YouTube Video',
                            'type' => 'youtube',
                            'content' => $resource
                        ];
                    } else {
                        $newResources[] = [
                            'title' => 'Resource Link',
                            'type' => 'link',
                            'content' => $resource
                        ];
                    }
                }
            }

            DB::table('track_curriculum_activities')
                ->where('id', $activity->id)
                ->update(['resources' => json_encode($newResources)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Down migration converts objects back to simple strings
        $activities = DB::table('track_curriculum_activities')
            ->whereNotNull('resources')
            ->get();

        foreach ($activities as $activity) {
            $resources = json_decode($activity->resources, true);
            if (!is_array($resources)) continue;

            $oldResources = [];
            foreach ($resources as $resource) {
                if (is_array($resource) && isset($resource['content'])) {
                    $oldResources[] = $resource['content'];
                } elseif (is_string($resource)) {
                    $oldResources[] = $resource;
                }
            }

            DB::table('track_curriculum_activities')
                ->where('id', $activity->id)
                ->update(['resources' => json_encode($oldResources)]);
        }
    }
};

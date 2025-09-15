<?php

namespace Fuelviews\SabHeroEstimator\Traits;

use Fuelviews\SabHeroEstimator\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasEstimatorProjects
{
    /**
     * Get all estimator projects for this user
     */
    public function estimatorProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'email', 'email');
    }

    /**
     * Get interior projects only
     */
    public function interiorProjects(): HasMany
    {
        return $this->estimatorProjects()->where('project_type', 'interior');
    }

    /**
     * Get exterior projects only
     */
    public function exteriorProjects(): HasMany
    {
        return $this->estimatorProjects()->where('project_type', 'exterior');
    }

    /**
     * Get recent estimator projects
     */
    public function recentEstimatorProjects(int $limit = 5): HasMany
    {
        return $this->estimatorProjects()
            ->latest()
            ->limit($limit);
    }
}

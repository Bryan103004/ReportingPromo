<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Log user create action
     * @param Model|string $model  - pass Eloquent model instance agar subject_type & subject_id terisi
     */
    public static function logCreate($model, $recordId, $data, $description = null)
    {
        $modelClass = $model instanceof Model ? class_basename($model) : $model;

        $logger = activity('user_activities')
            ->withProperties([
                'action'    => 'create',
                'record_id' => $recordId,
                'data'      => $data,
            ]);

        if ($model instanceof Model) {
            $logger->performedOn($model);
        }

        $logger->log($description ?? "Created {$modelClass} record");
    }

    /**
     * Log user update action
     * @param Model|string $model  - pass Eloquent model instance agar subject_type & subject_id terisi
     */
    public static function logUpdate($model, $recordId, $data, $description = null)
    {
        $modelClass = $model instanceof Model ? class_basename($model) : $model;

        $logger = activity('user_activities')
            ->withProperties([
                'action'    => 'update',
                'record_id' => $recordId,
                'data'      => $data,
            ]);

        if ($model instanceof Model) {
            $logger->performedOn($model);
        }

        $logger->log($description ?? "Updated {$modelClass} record");
    }

    /**
     * Log user delete action
     * @param Model|string $model  - pass Eloquent model instance agar subject_type & subject_id terisi
     */
    public static function logDelete($model, $recordId, $data, $description = null)
    {
        $modelClass = $model instanceof Model ? class_basename($model) : $model;

        $logger = activity('user_activities')
            ->withProperties([
                'action'    => 'delete',
                'record_id' => $recordId,
                'data'      => $data,
            ]);

        if ($model instanceof Model) {
            $logger->performedOn($model);
        }

        $logger->log($description ?? "Deleted {$modelClass} record");
    }

    /**
     * Log user view/download action
     */
    public static function logView($action, $details, $description = null)
    {
        activity('user_activities')
            ->withProperties([
                'action'  => $action,
                'details' => $details,
            ])
            ->log($description ?? "User performed action: {$action}");
    }

    /**
     * Log error with full stack trace
     */
    public static function logError($message, $exception = null, $context = [])
    {
        Log::error($message, array_merge($context, [
            'exception' => $exception ? $exception->getMessage() : null,
            'trace'     => $exception ? $exception->getTraceAsString() : null,
        ]));
    }

    /**
     * Log warning
     */
    public static function logWarning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    /**
     * Log info
     */
    public static function logInfo($message, $context = [])
    {
        Log::info($message, $context);
    }
}
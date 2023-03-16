<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Define response macros for commonly used HTTP status codes
        Response::macro('success', function ($data = [], $message = '') {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ], ResponseAlias::HTTP_OK);
        });

        Response::macro('created', function ($data = [], $message = '') {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ], ResponseAlias::HTTP_CREATED);
        });

        Response::macro('noContent', function ($message = '') {
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ], ResponseAlias::HTTP_NO_CONTENT);
        });

        Response::macro('badRequest', function ($message = '') {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], ResponseAlias::HTTP_BAD_REQUEST);
        });

        Response::macro('unauthorized', function ($message = '') {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        });

        Response::macro('forbidden', function ($message = '') {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], ResponseAlias::HTTP_FORBIDDEN);
        });

        Response::macro('notFound', function ($message = '') {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], ResponseAlias::HTTP_NOT_FOUND);
        });

        Response::macro('internalServerError', function ($message = '') {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        });

        // Define a new macro for the Eloquent builder called 'whereLike'.
        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(str_contains($attribute, '.'), function (Builder $query) use ($attribute, $searchTerm) {
                        [$relationName, $relationAttribute] = explode('.', $attribute);

                        $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                            $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                        });
                    }, function (Builder $query) use ($attribute, $searchTerm) {
                        $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                    });
                }
            });

            // Return the builder instance for method chaining.
            return $this;
        });

        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->mixedCase()->letters()->symbols()->uncompromised()
                : $rule;
        });

        Model::shouldBeStrict(! $this->app->isProduction());
    }
}

import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
const VersionController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: VersionController.url(options),
    method: 'get',
})

VersionController.definition = {
    methods: ["get","head"],
    url: '/version',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
VersionController.url = (options?: RouteQueryOptions) => {
    return VersionController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
VersionController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: VersionController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
VersionController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: VersionController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
const VersionControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: VersionController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
VersionControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: VersionController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\VersionController::__invoke
* @see app/Http/Controllers/VersionController.php:10
* @route '/version'
*/
VersionControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: VersionController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

VersionController.form = VersionControllerForm

export default VersionController
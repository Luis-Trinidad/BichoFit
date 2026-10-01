import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
const ProgressController969148ad13eb7575c5e2a15b7adfcc2c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ProgressController969148ad13eb7575c5e2a15b7adfcc2c.url(options),
    method: 'get',
})

ProgressController969148ad13eb7575c5e2a15b7adfcc2c.definition = {
    methods: ["get","head"],
    url: '/progress',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
ProgressController969148ad13eb7575c5e2a15b7adfcc2c.url = (options?: RouteQueryOptions) => {
    return ProgressController969148ad13eb7575c5e2a15b7adfcc2c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
ProgressController969148ad13eb7575c5e2a15b7adfcc2c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ProgressController969148ad13eb7575c5e2a15b7adfcc2c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
ProgressController969148ad13eb7575c5e2a15b7adfcc2c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ProgressController969148ad13eb7575c5e2a15b7adfcc2c.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
const ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.url(args, options),
    method: 'get',
})

ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.definition = {
    methods: ["get","head"],
    url: '/progress/{exercise}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.url = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { exercise: args }
    }

    if (Array.isArray(args)) {
        args = {
            exercise: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        exercise: args.exercise,
    }

    return ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.definition.url
            .replace('{exercise}', parsedArgs.exercise.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.get = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.head = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f.url(args, options),
    method: 'head',
})

/**
* Multiple routes resolve to \App\Http\Controllers\ProgressController::ProgressController, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `ProgressController['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const ProgressController = {
    '/progress': ProgressController969148ad13eb7575c5e2a15b7adfcc2c,
    '/progress/{exercise}': ProgressControllerbe73b85ff4cf9d027c5ec899e2043e1f,
}

export default ProgressController
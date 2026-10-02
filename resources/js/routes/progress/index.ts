import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/progress',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
export const exercise = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exercise.url(args, options),
    method: 'get',
})

exercise.definition = {
    methods: ["get","head"],
    url: '/progress/{exercise}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
exercise.url = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return exercise.definition.url
            .replace('{exercise}', parsedArgs.exercise.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
exercise.get = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exercise.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
exercise.head = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exercise.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
const exerciseForm = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exercise.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
exerciseForm.get = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exercise.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ProgressController::__invoke
* @see app/Http/Controllers/ProgressController.php:12
* @route '/progress/{exercise}'
*/
exerciseForm.head = (args: { exercise: string | number } | [exercise: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exercise.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exercise.form = exerciseForm

const progress = {
    show: Object.assign(show, show),
    exercise: Object.assign(exercise, exercise),
}

export default progress
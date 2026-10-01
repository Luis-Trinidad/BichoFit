import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ExerciseController::index
* @see app/Http/Controllers/ExerciseController.php:13
* @route '/exercises'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/exercises',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ExerciseController::index
* @see app/Http/Controllers/ExerciseController.php:13
* @route '/exercises'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExerciseController::index
* @see app/Http/Controllers/ExerciseController.php:13
* @route '/exercises'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ExerciseController::index
* @see app/Http/Controllers/ExerciseController.php:13
* @route '/exercises'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ExerciseController::show
* @see app/Http/Controllers/ExerciseController.php:38
* @route '/exercises/{exercise}'
*/
export const show = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/exercises/{exercise}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ExerciseController::show
* @see app/Http/Controllers/ExerciseController.php:38
* @route '/exercises/{exercise}'
*/
show.url = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { exercise: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { exercise: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            exercise: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        exercise: typeof args.exercise === 'object'
        ? args.exercise.id
        : args.exercise,
    }

    return show.definition.url
            .replace('{exercise}', parsedArgs.exercise.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExerciseController::show
* @see app/Http/Controllers/ExerciseController.php:38
* @route '/exercises/{exercise}'
*/
show.get = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ExerciseController::show
* @see app/Http/Controllers/ExerciseController.php:38
* @route '/exercises/{exercise}'
*/
show.head = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ExerciseController::store
* @see app/Http/Controllers/ExerciseController.php:55
* @route '/exercises'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/exercises',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ExerciseController::store
* @see app/Http/Controllers/ExerciseController.php:55
* @route '/exercises'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExerciseController::store
* @see app/Http/Controllers/ExerciseController.php:55
* @route '/exercises'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ExerciseController::destroy
* @see app/Http/Controllers/ExerciseController.php:62
* @route '/exercises/{exercise}'
*/
export const destroy = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/exercises/{exercise}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\ExerciseController::destroy
* @see app/Http/Controllers/ExerciseController.php:62
* @route '/exercises/{exercise}'
*/
destroy.url = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { exercise: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { exercise: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            exercise: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        exercise: typeof args.exercise === 'object'
        ? args.exercise.id
        : args.exercise,
    }

    return destroy.definition.url
            .replace('{exercise}', parsedArgs.exercise.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExerciseController::destroy
* @see app/Http/Controllers/ExerciseController.php:62
* @route '/exercises/{exercise}'
*/
destroy.delete = (args: { exercise: number | { id: number } } | [exercise: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const ExerciseController = { index, show, store, destroy }

export default ExerciseController
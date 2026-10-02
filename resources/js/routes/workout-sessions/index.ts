import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Workout\SessionController::store
* @see app/Http/Controllers/Workout/SessionController.php:17
* @route '/workout-sessions'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/workout-sessions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Workout\SessionController::store
* @see app/Http/Controllers/Workout/SessionController.php:17
* @route '/workout-sessions'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SessionController::store
* @see app/Http/Controllers/Workout/SessionController.php:17
* @route '/workout-sessions'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::store
* @see app/Http/Controllers/Workout/SessionController.php:17
* @route '/workout-sessions'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::store
* @see app/Http/Controllers/Workout/SessionController.php:17
* @route '/workout-sessions'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
export const show = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/workout-sessions/{session}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
show.url = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { session: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { session: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            session: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        session: typeof args.session === 'object'
        ? args.session.id
        : args.session,
    }

    return show.definition.url
            .replace('{session}', parsedArgs.session.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
show.get = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
show.head = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
const showForm = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
showForm.get = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::show
* @see app/Http/Controllers/Workout/SessionController.php:53
* @route '/workout-sessions/{session}'
*/
showForm.head = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Workout\SessionController::finish
* @see app/Http/Controllers/Workout/SessionController.php:133
* @route '/workout-sessions/{session}/finish'
*/
export const finish = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finish.url(args, options),
    method: 'post',
})

finish.definition = {
    methods: ["post"],
    url: '/workout-sessions/{session}/finish',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Workout\SessionController::finish
* @see app/Http/Controllers/Workout/SessionController.php:133
* @route '/workout-sessions/{session}/finish'
*/
finish.url = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { session: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { session: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            session: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        session: typeof args.session === 'object'
        ? args.session.id
        : args.session,
    }

    return finish.definition.url
            .replace('{session}', parsedArgs.session.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SessionController::finish
* @see app/Http/Controllers/Workout/SessionController.php:133
* @route '/workout-sessions/{session}/finish'
*/
finish.post = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::finish
* @see app/Http/Controllers/Workout/SessionController.php:133
* @route '/workout-sessions/{session}/finish'
*/
const finishForm = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: finish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::finish
* @see app/Http/Controllers/Workout/SessionController.php:133
* @route '/workout-sessions/{session}/finish'
*/
finishForm.post = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: finish.url(args, options),
    method: 'post',
})

finish.form = finishForm

/**
* @see \App\Http\Controllers\Workout\SessionController::destroy
* @see app/Http/Controllers/Workout/SessionController.php:142
* @route '/workout-sessions/{session}'
*/
export const destroy = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/workout-sessions/{session}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Workout\SessionController::destroy
* @see app/Http/Controllers/Workout/SessionController.php:142
* @route '/workout-sessions/{session}'
*/
destroy.url = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { session: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { session: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            session: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        session: typeof args.session === 'object'
        ? args.session.id
        : args.session,
    }

    return destroy.definition.url
            .replace('{session}', parsedArgs.session.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SessionController::destroy
* @see app/Http/Controllers/Workout/SessionController.php:142
* @route '/workout-sessions/{session}'
*/
destroy.delete = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::destroy
* @see app/Http/Controllers/Workout/SessionController.php:142
* @route '/workout-sessions/{session}'
*/
const destroyForm = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SessionController::destroy
* @see app/Http/Controllers/Workout/SessionController.php:142
* @route '/workout-sessions/{session}'
*/
destroyForm.delete = (args: { session: number | { id: number } } | [session: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const workoutSessions = {
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    finish: Object.assign(finish, finish),
    destroy: Object.assign(destroy, destroy),
}

export default workoutSessions
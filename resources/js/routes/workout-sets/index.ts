import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Workout\SetController::store
* @see app/Http/Controllers/Workout/SetController.php:13
* @route '/workout-sessions/{session}/sets'
*/
export const store = (args: { session: string | number } | [session: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/workout-sessions/{session}/sets',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Workout\SetController::store
* @see app/Http/Controllers/Workout/SetController.php:13
* @route '/workout-sessions/{session}/sets'
*/
store.url = (args: { session: string | number } | [session: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { session: args }
    }

    if (Array.isArray(args)) {
        args = {
            session: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        session: args.session,
    }

    return store.definition.url
            .replace('{session}', parsedArgs.session.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SetController::store
* @see app/Http/Controllers/Workout/SetController.php:13
* @route '/workout-sessions/{session}/sets'
*/
store.post = (args: { session: string | number } | [session: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SetController::store
* @see app/Http/Controllers/Workout/SetController.php:13
* @route '/workout-sessions/{session}/sets'
*/
const storeForm = (args: { session: string | number } | [session: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SetController::store
* @see app/Http/Controllers/Workout/SetController.php:13
* @route '/workout-sessions/{session}/sets'
*/
storeForm.post = (args: { session: string | number } | [session: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Workout\SetController::update
* @see app/Http/Controllers/Workout/SetController.php:22
* @route '/workout-sets/{set}'
*/
export const update = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/workout-sets/{set}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Workout\SetController::update
* @see app/Http/Controllers/Workout/SetController.php:22
* @route '/workout-sets/{set}'
*/
update.url = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { set: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { set: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            set: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        set: typeof args.set === 'object'
        ? args.set.id
        : args.set,
    }

    return update.definition.url
            .replace('{set}', parsedArgs.set.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SetController::update
* @see app/Http/Controllers/Workout/SetController.php:22
* @route '/workout-sets/{set}'
*/
update.patch = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Workout\SetController::update
* @see app/Http/Controllers/Workout/SetController.php:22
* @route '/workout-sets/{set}'
*/
const updateForm = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SetController::update
* @see app/Http/Controllers/Workout/SetController.php:22
* @route '/workout-sets/{set}'
*/
updateForm.patch = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Workout\SetController::destroy
* @see app/Http/Controllers/Workout/SetController.php:31
* @route '/workout-sets/{set}'
*/
export const destroy = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/workout-sets/{set}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Workout\SetController::destroy
* @see app/Http/Controllers/Workout/SetController.php:31
* @route '/workout-sets/{set}'
*/
destroy.url = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { set: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { set: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            set: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        set: typeof args.set === 'object'
        ? args.set.id
        : args.set,
    }

    return destroy.definition.url
            .replace('{set}', parsedArgs.set.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\SetController::destroy
* @see app/Http/Controllers/Workout/SetController.php:31
* @route '/workout-sets/{set}'
*/
destroy.delete = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Workout\SetController::destroy
* @see app/Http/Controllers/Workout/SetController.php:31
* @route '/workout-sets/{set}'
*/
const destroyForm = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Workout\SetController::destroy
* @see app/Http/Controllers/Workout/SetController.php:31
* @route '/workout-sets/{set}'
*/
destroyForm.delete = (args: { set: number | { id: number } } | [set: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const workoutSets = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default workoutSets
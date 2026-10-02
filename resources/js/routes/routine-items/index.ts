import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\RoutineItemController::store
* @see app/Http/Controllers/RoutineItemController.php:13
* @route '/routines/{routine}/items'
*/
export const store = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/routines/{routine}/items',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoutineItemController::store
* @see app/Http/Controllers/RoutineItemController.php:13
* @route '/routines/{routine}/items'
*/
store.url = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { routine: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { routine: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            routine: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        routine: typeof args.routine === 'object'
        ? args.routine.id
        : args.routine,
    }

    return store.definition.url
            .replace('{routine}', parsedArgs.routine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineItemController::store
* @see app/Http/Controllers/RoutineItemController.php:13
* @route '/routines/{routine}/items'
*/
store.post = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineItemController::store
* @see app/Http/Controllers/RoutineItemController.php:13
* @route '/routines/{routine}/items'
*/
const storeForm = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineItemController::store
* @see app/Http/Controllers/RoutineItemController.php:13
* @route '/routines/{routine}/items'
*/
storeForm.post = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\RoutineItemController::update
* @see app/Http/Controllers/RoutineItemController.php:35
* @route '/routine-items/{item}'
*/
export const update = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/routine-items/{item}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\RoutineItemController::update
* @see app/Http/Controllers/RoutineItemController.php:35
* @route '/routine-items/{item}'
*/
update.url = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return update.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineItemController::update
* @see app/Http/Controllers/RoutineItemController.php:35
* @route '/routine-items/{item}'
*/
update.patch = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\RoutineItemController::update
* @see app/Http/Controllers/RoutineItemController.php:35
* @route '/routine-items/{item}'
*/
const updateForm = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineItemController::update
* @see app/Http/Controllers/RoutineItemController.php:35
* @route '/routine-items/{item}'
*/
updateForm.patch = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\RoutineItemController::destroy
* @see app/Http/Controllers/RoutineItemController.php:57
* @route '/routine-items/{item}'
*/
export const destroy = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/routine-items/{item}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\RoutineItemController::destroy
* @see app/Http/Controllers/RoutineItemController.php:57
* @route '/routine-items/{item}'
*/
destroy.url = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { item: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { item: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            item: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return destroy.definition.url
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineItemController::destroy
* @see app/Http/Controllers/RoutineItemController.php:57
* @route '/routine-items/{item}'
*/
destroy.delete = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\RoutineItemController::destroy
* @see app/Http/Controllers/RoutineItemController.php:57
* @route '/routine-items/{item}'
*/
const destroyForm = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineItemController::destroy
* @see app/Http/Controllers/RoutineItemController.php:57
* @route '/routine-items/{item}'
*/
destroyForm.delete = (args: { item: number | { id: number } } | [item: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const routineItems = {
    store: Object.assign(store, store),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default routineItems
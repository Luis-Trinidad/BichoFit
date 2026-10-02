import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/routines',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::index
* @see app/Http/Controllers/RoutineController.php:15
* @route '/routines'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\RoutineController::store
* @see app/Http/Controllers/RoutineController.php:41
* @route '/routines'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/routines',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoutineController::store
* @see app/Http/Controllers/RoutineController.php:41
* @route '/routines'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineController::store
* @see app/Http/Controllers/RoutineController.php:41
* @route '/routines'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineController::store
* @see app/Http/Controllers/RoutineController.php:41
* @route '/routines'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineController::store
* @see app/Http/Controllers/RoutineController.php:41
* @route '/routines'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
export const show = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/routines/{routine}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
show.url = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{routine}', parsedArgs.routine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
show.get = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
show.head = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
const showForm = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
showForm.get = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RoutineController::show
* @see app/Http/Controllers/RoutineController.php:48
* @route '/routines/{routine}'
*/
showForm.head = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\RoutineController::update
* @see app/Http/Controllers/RoutineController.php:89
* @route '/routines/{routine}'
*/
export const update = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/routines/{routine}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\RoutineController::update
* @see app/Http/Controllers/RoutineController.php:89
* @route '/routines/{routine}'
*/
update.url = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{routine}', parsedArgs.routine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineController::update
* @see app/Http/Controllers/RoutineController.php:89
* @route '/routines/{routine}'
*/
update.patch = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\RoutineController::update
* @see app/Http/Controllers/RoutineController.php:89
* @route '/routines/{routine}'
*/
const updateForm = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineController::update
* @see app/Http/Controllers/RoutineController.php:89
* @route '/routines/{routine}'
*/
updateForm.patch = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\RoutineController::destroy
* @see app/Http/Controllers/RoutineController.php:104
* @route '/routines/{routine}'
*/
export const destroy = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/routines/{routine}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\RoutineController::destroy
* @see app/Http/Controllers/RoutineController.php:104
* @route '/routines/{routine}'
*/
destroy.url = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{routine}', parsedArgs.routine.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoutineController::destroy
* @see app/Http/Controllers/RoutineController.php:104
* @route '/routines/{routine}'
*/
destroy.delete = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\RoutineController::destroy
* @see app/Http/Controllers/RoutineController.php:104
* @route '/routines/{routine}'
*/
const destroyForm = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoutineController::destroy
* @see app/Http/Controllers/RoutineController.php:104
* @route '/routines/{routine}'
*/
destroyForm.delete = (args: { routine: number | { id: number } } | [routine: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const RoutineController = { index, store, show, update, destroy }

export default RoutineController
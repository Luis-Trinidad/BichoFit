import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Workout\HistoryController::index
* @see app/Http/Controllers/Workout/HistoryController.php:12
* @route '/history'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Workout\HistoryController::index
* @see app/Http/Controllers/Workout/HistoryController.php:12
* @route '/history'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Workout\HistoryController::index
* @see app/Http/Controllers/Workout/HistoryController.php:12
* @route '/history'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Workout\HistoryController::index
* @see app/Http/Controllers/Workout/HistoryController.php:12
* @route '/history'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

const history = {
    index: Object.assign(index, index),
}

export default history
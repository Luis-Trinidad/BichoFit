import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BodyScanController::index
* @see app/Http/Controllers/BodyScanController.php:16
* @route '/body-scans'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/body-scans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BodyScanController::index
* @see app/Http/Controllers/BodyScanController.php:16
* @route '/body-scans'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BodyScanController::index
* @see app/Http/Controllers/BodyScanController.php:16
* @route '/body-scans'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BodyScanController::index
* @see app/Http/Controllers/BodyScanController.php:16
* @route '/body-scans'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BodyScanController::ocr
* @see app/Http/Controllers/BodyScanController.php:32
* @route '/body-scans/ocr'
*/
export const ocr = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ocr.url(options),
    method: 'post',
})

ocr.definition = {
    methods: ["post"],
    url: '/body-scans/ocr',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BodyScanController::ocr
* @see app/Http/Controllers/BodyScanController.php:32
* @route '/body-scans/ocr'
*/
ocr.url = (options?: RouteQueryOptions) => {
    return ocr.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BodyScanController::ocr
* @see app/Http/Controllers/BodyScanController.php:32
* @route '/body-scans/ocr'
*/
ocr.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ocr.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BodyScanController::store
* @see app/Http/Controllers/BodyScanController.php:55
* @route '/body-scans'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/body-scans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BodyScanController::store
* @see app/Http/Controllers/BodyScanController.php:55
* @route '/body-scans'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BodyScanController::store
* @see app/Http/Controllers/BodyScanController.php:55
* @route '/body-scans'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BodyScanController::destroy
* @see app/Http/Controllers/BodyScanController.php:66
* @route '/body-scans/{scan}'
*/
export const destroy = (args: { scan: number | { id: number } } | [scan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/body-scans/{scan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\BodyScanController::destroy
* @see app/Http/Controllers/BodyScanController.php:66
* @route '/body-scans/{scan}'
*/
destroy.url = (args: { scan: number | { id: number } } | [scan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { scan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { scan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            scan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        scan: typeof args.scan === 'object'
        ? args.scan.id
        : args.scan,
    }

    return destroy.definition.url
            .replace('{scan}', parsedArgs.scan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BodyScanController::destroy
* @see app/Http/Controllers/BodyScanController.php:66
* @route '/body-scans/{scan}'
*/
destroy.delete = (args: { scan: number | { id: number } } | [scan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const BodyScanController = { index, ocr, store, destroy }

export default BodyScanController
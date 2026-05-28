@include('errors.layout', [
    'code' => 404,
    'title' => __('Page not found'),
    'body' => __('The page you were looking for is no longer available, or the link is incorrect.'),
])

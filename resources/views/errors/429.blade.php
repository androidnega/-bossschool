@include('errors.layout', [
    'code' => 429,
    'title' => __('Too many requests'),
    'body' => __('You have made too many requests in a short time. Wait a minute and try again.'),
])

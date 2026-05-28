@include('errors.layout', [
    'code' => 500,
    'title' => __('Something went wrong'),
    'body' => __('We hit an unexpected error. Our team has been notified. Try again in a minute, and if the problem persists send your school admin the request ID below.'),
    'request_id' => $request_id ?? request()->headers->get('X-Request-Id'),
])

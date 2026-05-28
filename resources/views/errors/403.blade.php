@include('errors.layout', [
    'code' => 403,
    'title' => __('Permission denied'),
    'body' => __('You do not have permission to access this page. Ask your school admin if you believe this is a mistake.'),
])

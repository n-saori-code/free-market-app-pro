@if($user->profile && $user->profile->profile_image)
<img
    src="{{ asset('storage/' . $user->profile->profile_image) }}"
    class="avatar"
    alt="{{ $user->name }}">
@else
<div class="avatar"></div>
@endif
@php
$user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
<x-filament::section style="background-color: #3fa4f6;">
<div class="flex items-center gap-x-3">
<x-filament-panels::avatar.user size="lg" :user="$user"/>

<div class="flex-1">
<h2
class="grid flex-1 font-bold font-semibold leading-6 text-white text-base"
>
{{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}, {{ filament()->getUserName($user) }}
</h2>

</div>

<form
action="{{ filament()->getLogoutUrl() }}"
method="post"
class="my-auto"
>
@csrf

<x-filament::button
color="gray"
icon="heroicon-m-arrow-left-on-rectangle"
icon-alias="panels::widgets.account.logout-button"
labeled-from="sm"
tag="button"
type="submit"
>
{{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
</x-filament::button>
</form>
</div>
</x-filament::section>
</x-filament-widgets::widget>

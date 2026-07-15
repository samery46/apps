@props(['activeTab'])

<div class="custom-tabs">
    <a href="{{ request()->fullUrlWithQuery(['plant_id' => 'all']) }}"
        class="{{ $activeTab === 'all' ? 'active' : '' }}">All Perangkat</a>
    <a href="{{ request()->fullUrlWithQuery(['plant_id' => '1']) }}"
        class="{{ $activeTab === '1' ? 'active' : '' }}">TSP</a>
    <a href="{{ request()->fullUrlWithQuery(['plant_id' => '28']) }}"
        class="{{ $activeTab === '28' ? 'active' : '' }}">AIBM</a>
</div>

<style>
    .custom-tabs a {
        padding: 10px;
        text-decoration: none;
        color: black;
        display: inline-block;
        margin-right: 10px;
        border-bottom: 2px solid transparent;
    }

    .custom-tabs a.active {
        font-weight: bold;
        color: blue;
        border-bottom: 2px solid blue;
    }
</style>

<div>
    <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
    <select id="client_id" name="client_id" x-model="{{ $shift }}.client_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
        <option value="">Select a Client</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
        @endforeach
    </select>
</div>
<!-- Caregiver -->
<div>
    <label for="caregiver_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Caregiver</label>
    <select id="caregiver_id" name="caregiver_id" x-model="{{ $shift }}.caregiver_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
        <option value="">Select a Caregiver</option>
        @foreach($caregivers as $caregiver)
            <option value="{{ $caregiver->id }}">{{ $caregiver->first_name }} {{ $caregiver->last_name }}</option>
        @endforeach
    </select>
</div>
<!-- Start Time -->
<div>
    <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
    <input type="datetime-local" id="start_time" name="start_time" x-model="{{ $shift }}.start_time" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:[color-scheme:dark]">
</div>
<!-- End Time -->
<div>
    <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
    <input type="datetime-local" id="end_time" name="end_time" x-model="{{ $shift }}.end_time" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm dark:[color-scheme:dark]">
</div>
<!-- Notes -->
<div class="md:col-span-2">
    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <textarea id="notes" name="notes" x-model="{{ $shift }}.notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
</div>

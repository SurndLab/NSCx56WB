<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    public function index()
    {
        return Publisher::with('contacts')->where('is_active', true)->paginate(20);
    }

    public function inactive()
    {
        return Publisher::with('contacts')->where('is_active', false)->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'publisher_name' => ['required', 'string', 'max:255'],
            'publisher_address' => ['required', 'string', 'max:255'],
            'publisher_phone' => ['required', 'string', 'max:32'],
            'publisher_isbn_code' => ['required', 'string', 'max:12', 'unique:publishers,publisher_isbn_code'],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.contact_name' => ['required', 'string', 'max:255'],
            'contacts.*.contact_phone' => ['required', 'string', 'max:32'],
            'contacts.*.contact_email' => ['required', 'email', 'max:255'],
        ]);

        $publisher = DB::transaction(function () use ($data) {
            $publisher = Publisher::create([
                'publisher_name' => $data['publisher_name'],
                'publisher_address' => $data['publisher_address'],
                'publisher_phone' => $data['publisher_phone'],
                'publisher_isbn_code' => $data['publisher_isbn_code'],
                'is_active' => true,
            ]);

            $publisher->contacts()->createMany($data['contacts']);

            return $publisher;
        });

        return response()->json($publisher->load('contacts'), 201);
    }

    public function update(Request $request, Publisher $publisher)
    {
        $data = $request->validate([
            'publisher_name' => ['required', 'string', 'max:255'],
            'publisher_address' => ['required', 'string', 'max:255'],
            'publisher_phone' => ['required', 'string', 'max:32'],
            'publisher_isbn_code' => ['required', 'string', 'max:12', 'unique:publishers,publisher_isbn_code,' . $publisher->id],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.contact_name' => ['required', 'string', 'max:255'],
            'contacts.*.contact_phone' => ['required', 'string', 'max:32'],
            'contacts.*.contact_email' => ['required', 'email', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $publisher) {
            $publisher->update([
                'publisher_name' => $data['publisher_name'],
                'publisher_address' => $data['publisher_address'],
                'publisher_phone' => $data['publisher_phone'],
                'publisher_isbn_code' => $data['publisher_isbn_code'],
            ]);

            $publisher->contacts()->delete();
            $publisher->contacts()->createMany($data['contacts']);
        });

        return response()->json($publisher->load('contacts'));
    }

    public function disable(Publisher $publisher)
    {
        $publisher->update(['is_active' => false]);

        return response()->json(['message' => 'Publisher disabled']);
    }

    public function enable(Publisher $publisher)
    {
        $publisher->update(['is_active' => true]);

        return response()->json(['message' => 'Publisher enabled']);
    }
}

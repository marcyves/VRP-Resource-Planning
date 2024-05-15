<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class DocumentController extends Controller
{
    protected $directory = "data_store/";
    public function show(Document $document)
    {
        $pathToFile = "storage/".$this->directory.$document->file_name;

        return response()->file($pathToFile);
        //return response()->download($pathToFile, 'test.pdf', $headers);
    }

    public function edit()
    {
        //
    }

    public function store(Request $request, String $school_id)
    {
        $request->validate([
            'description' => 'required',
            'document' => 'required|mimes:pdf,doc,docx|max:4096'
        ]);

        $fileName = time() . '_' . $request->document->getClientOriginalName();
        $request->document->storeAs("public/".$this->directory, $fileName);

        Document::create([
            'description' => $request->description,
            'year' => $request->year,
            'school_id' => $school_id,
            'file_name' => $fileName
        ]);

        session()->flash('success', "Document added successfully.");

        return redirect()->back();
    }

    public function destroy(Document $document)
    {
        Storage::delete("public/".$this->directory.$document->file_name);
        $document->delete();

        session()->flash('success', "Document deleted successfully.");

        return redirect()->back();
    }

    public function delete(Document $document)
    {
        return view('document.delete', compact('document'));
    }
}

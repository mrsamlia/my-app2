<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Menampilkan semua produk
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate= $request->validate([
            'name'  => 'required|max:50',
            'image' => 'nullable|mimes:png,jpg',
            'price' => 'required|max:20',
            'description' => 'required'
        ]);

        // Upload image jika ada
        if ($request->file) {
            $image = $request->file;
            $image->storeAs('public/products', $image->hashName()); // Menyimpan file
            $upload = $image->hashName();
        }else{
            $upload = '';
        }

        $products = Product::create([
            'name'          => $request->name,
            'image'         => $upload,
            'slug'          => Str::slug($request->name, '-'),
            'price'         => $request->price,
            'description'   => $request->description
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan!',
            'data' => $products,
            'redirect' => url('/products')
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $products = Product::findOrFail($id);

        return response()->json($products);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate= $request->validate([
            'name'  => 'nullable|max:50',
            'image' => 'nullable|mimes:png,jpg',
            'price' => 'required|max:20',
            'description' => 'required'
        ]);

        $product = Product::findOrFail($id);

        // Upload image jika ada
        if ($request->hasFile('image')) {
            //remove old image
            Storage::delete('public/products/'.basename($product->image));

            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName()); // Menyimpan file
            $upload = $image->hashName();
            $product->update([
                'image'         => $upload,
            ]);
        }

        $product->update([
            'name'          => $request->input('name', $product->name),
            'slug'          => Str::slug($request->name, '-'),
            'price'         => $request->price,
            'description'   => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diubah!',
            'data' => $product,
            'redirect' => url('/products')
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $products = Product::findOrFail($id);

        try {
            $products->delete();
            Storage::disk('local')->delete('public/products/'.basename($products->image));
          } catch (Exception $e) {
            return response()->json([
                'warn_msg' => 'Data tidak dapat dihapus!',
                'warn_desc' => 'data ini memiliki relasi dengan data lain. Hapus terlebih dahulu data tersebut.',
            ]);
        ;}
        
          return response()->json([
            'success' => true,
            'msg' => 'Data berhasil dihapus!.',
            'txt' => 'Data yang anda pilih berhasil dihapus.',
        ]);
    }
}

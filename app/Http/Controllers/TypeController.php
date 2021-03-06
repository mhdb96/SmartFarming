<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Type;
use App\Models\Category;
use DB;

class TypeData{
    public $id;
    public $name;
    public $category;
}

class TypeController extends Controller
{
    private $route = 'type';
    private $title = 'Tip';
    private $fillables = ['name','category'];
    private $fillables_titles = ['İsim','Kategori'];
    private $fillables_types = ['text','one'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::all();

        $data =array();
        foreach($types as $item){
            $d = new TypeData();
            $d->id = $item->id;
            $d->name = $item->name;
            $d->category = $item->category->name;
            array_push($data,$d);
        }

        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => $this->fillables,
            'fillables_titles' => $this->fillables_titles,
            'empty_space' => 1000,
            'data' => $data
        );
        return view($this->route.'.index')->with($my_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $categories = Category::all();        
        if(count($categories) == 0)
            return redirect()->route('category.create');
        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => ['name', $categories],
            'fillables_titles' => ['İsim','Kategori'],
            'fillables_types' => $this->fillables_types,
            'is_multiple' => false
        );        
        return view($this->route.'.create')->with($my_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $type = new Type;
        $type->name = $request->name;        
        $type->category()->associate($request->categories);     
        $type->save();                         
        return redirect()->route($this->route.'.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Type $type)
    {
        $categories = Category::all();
        $insertedCategoryIds = array();                            
        array_push($insertedCategoryIds, $type->category->id);
        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => ['name',[$categories, $insertedCategoryIds] ],
            'fillables_titles' => ['İsim','Kategori'],  
            'fillables_types' => $this->fillables_types,          
            'data' => $type
        );
        return view($this->route.'.edit')->with($my_data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Type $type)
    {
        $type->name = $request->name;        
        $type->category()->associate($request->categories);          
        $type->save();        
        return redirect()->route($this->route.'.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $isExistPlant = DB::table('plants')->where('type_id', $id)->exists();
        $isExistUnit = DB::table('units')->where('type_id', $id)->exists();
        $isExistArea = DB::table('areas')->where('type_id', $id)->exists();
        if($isExistPlant | $isExistUnit | $isExistArea)
        {
            return redirect('/'.$this->route)
            ->with('warning', 'Bu '.$this->title.' türü diğer tablolarla ilişki olduğu için silemezsiniz.');
        }       


            Type::find($id)->delete();
            return redirect('/'.$this->route)
                ->with('success', $this->title.' silme işlemi başarılı bir şekilde gerçekleştirildi');
        /*
        $type->delete();
        return redirect()->route($this->route.'.index');*/
    }
}

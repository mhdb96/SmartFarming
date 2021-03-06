<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;
use App\Models\Unit;
use App\Models\Type;
use App\Models\RegionSoil;
use App\Models\SoilPlant;

class PlantData{
    public $id;
    public $name;
    public $unit_price;
    public $type;
    public $unit;
    public $regions = array();
}

class PlantController extends Controller
{
    private $route = 'plant';
    private $title = 'Bitki';
    private $fillables = ['name','unit_price','type','unit','regions'];
    private $fillables_titles = ['İsim','Fiyat','Türü','Birim','İklim ve Toprak Türleri'];
    private $fillables_types = ['text','number','one','one','many'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plants = Plant::all();
        $data = array();
        foreach($plants as $plant){
            $d = new PlantData();
            $d->id = $plant->id;
            $d->name = $plant->name;
            $d->unit_price = $plant->unit_price.'₺';
            $d->type = $plant->type->name;
            $d->unit = $plant->unit->name;
            $array = array();

            foreach($plant->regionSoils as $region){
                array_push($array, $region->region->name.' - '.$region->soil->name );
            }
            $d->regions = $array;
            array_push($data,$d);
        }

        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => $this->fillables,
            'fillables_titles' => $this->fillables_titles,
            'empty_space' => 700,
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
        $regionSoils = RegionSoil::all();
        $types = Type::where('category_id','=','10')->get();
                   
        $units = Unit::where('type_id','=','12')->get();    
        if(count($types) == 0)
            return redirect()->route('type.create');
        if(count($units) == 0)
            return redirect()->route('unit.create');
        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => ['name','unit_price' ,$types, $units, $regionSoils],
            'fillables_titles' => ['İsim','Fiyat','Bitki','Ölçü','İklim ve Toprak'],
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
        $plant = new Plant;
        $plant->name = $request->name;        
        $plant->unit_price = $request->unit_price;        
        $plant->type()->associate($request->types); 
        $plant->unit()->associate($request->units);                 
        $plant->save();   
        $plant->regionSoils()->attach($request->region_soil);                     
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
    public function edit(Plant $plant)
    {
        //$types = Type::all();
        $types = Type::where('category_id','=','10')->get();
                   
        $insertedTypesIds = array();                            
        array_push($insertedTypesIds, $plant->type->id);
        //$units = Unit::all();
        $units = Unit::where('type_id','=','12')->get();    

        $insertedUnitIds = array();                            
        array_push($insertedUnitIds, $plant->unit->id);
        $regionSoils = RegionSoil::all();
        $insertedRegionSoilIds = array();
        foreach ($plant->regionSoils as $regionSoil) {
            array_push($insertedRegionSoilIds, $regionSoil->id);
        }


        $my_data = array(
            'title' => $this->title,
            'route' => $this->route,
            'fillables' => ['name','unit_price',[$types, $insertedTypesIds], [$units, $insertedUnitIds], [$regionSoils, $insertedRegionSoilIds] ],
            'fillables_titles' => ['İsim','Fiyat','Bitki','Ölçü','İklim ve Toprak'], 
            'fillables_types' => $this->fillables_types,          
            'data' => $plant
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
    public function update(Request $request, Plant $plant)
    {
        $plant->name = $request->name;
        $plant->unit_price = $request->unit_price;
        $plant->type()->associate($request->types);          
        $plant->unit()->associate($request->units);
        $plant->save();  
        $plant->regionSoils()->sync($request->region_soil);      
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
        $isExist = DB::table('area_capacity')->where('plant_id', $id)->exists();
        if($isExist)
        {
            return redirect('/'.$this->route)
            ->with('warning', 'Bu '.$this->title.' türü diğer tablolarla ilişki olduğu için silemezsiniz.');
        }       

            Plant::find($id)->regionSoils()->detach();
            Plant::find($id)->delete();
            return redirect('/'.$this->route)
                ->with('success', $this->title.' silme işlemi başarılı bir şekilde gerçekleştirildi');

/*
        $plant->regionSoils()->detach();
        $plant->delete();
        return redirect()->route($this->route.'.index');*/
    }
}

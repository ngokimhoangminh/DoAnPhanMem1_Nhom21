<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use App\Models\BloodDonation;
use App\Models\Hospital;
use App\Models\SignupBlood;
use App\Models\BloodActual;
use App\Models\CategoryNews;
use App\Models\News;
session_start();
class SignupBloodController extends Controller
{
    //
	 public function check_login()
    {
    	$admin_id=Session::get('admin_id');
    	if($admin_id)
    	{
    		return Redirect::to('/dashboard');
    	}else
    	{
    		return Redirect::to('/admin')->send();
    	}
    }
    public function signup_blood()
   {
   		$customer_id=Session::get('customer_id');
    	if($customer_id)
    	{
        $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
    		$list_blood_donation=BloodDonation::orderBy('blood_donation_id','desc')->where('blood_finish_date','>',Carbon::now())->get();

    		return view('client.signupBlood.signup_blood')->with(compact('list_blood_donation','category_news'));
    	}else
    	{
    		return Redirect::to('/login')->send();
    	}
   		
   }
   public function sign_up_blood($bloodid)
   {
      $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
      $blood=BloodDonation::where('blood_donation_id',$bloodid)->first();
      $hospital=Hospital::where('hospital_id',$blood->hospital_id)->first();
      $data=[];
      
      $data[]=
      [
        'id'=>$blood->blood_donation_id,
        'name'=>$blood->blood_donation_name,
        'place'=>$blood->blood_donation_place,
        'object'=>$blood->blood_object,
        'hospital'=>$hospital->hospital_name,
        'time'=>date('h:i', strtotime($blood->blood_donation_time)).'--'.date('d-m-Y', strtotime($blood->blood_start_date))
      ];
      View::share('datas',$data);
      return view('client.signupBlood.sign_up_blood')->with(compact('category_news'));
   }
   public function filter_blood(Request $request)
   {
   		$blood_id=$request->blood_id;
   		$blood=BloodDonation::where('blood_donation_id',$blood_id)->first();
   		$hospital=Hospital::where('hospital_id',$blood->hospital_id)->first();
   		$data=[];
   		
		$data[]=
		[
			'place'=>$blood->blood_donation_place,
			'object'=>$blood->blood_object,
			'hospital'=>$hospital->hospital_name,
			'time'=>date('h:i', strtotime($blood->blood_donation_time)).'--'.date('d-m-Y', strtotime($blood->blood_start_date))
		];
   		return response()->json([
            'error'    => false,
            'response'=>$data,
            'messages' => "L??u th??nh c??ng",
        ], 200);
   }
   public function notification_sign_up_blood()
   {
      $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
   		return view('client.signupBlood.notification_blood')->with(compact('category_news'));
   }
   public function save_sign_up_blood(Request $request)
   {
   		$data=$request->all();
        $signup_blood=new SignupBlood();

        $customer_id=Session::get('customer_id');
        //new cho ph??p insert d???u li???u
        $signup_blood->blood_donation_id=$data['blood_donation_id'];
        $signup_blood->users_id=$customer_id;
        $signup_blood->signup_blood_weight=$data['signup_blood_weight'];
        $signup_blood->signup_blood_height=$data['signup_blood_height'];
        $signup_blood->signup_blood_landau=$data['landauhienmau'];
        $signup_blood->signup_blood_macbenh=$data['tungmaccacbenh'];
        $signup_blood->signup_blood_sutcan=$data['sutcan'];
        $signup_blood->signup_blood_noihach=$data['noihach'];
        $signup_blood->signup_blood_phauthuat=$data['phauthuat'];
        $signup_blood->signup_blood_xamminh=$data['xamminh'];
        $signup_blood->signup_blood_duoctruyenmau=$data['duoctruyenmau'];
        $signup_blood->signup_blood_matuy=$data['matuy'];
        $signup_blood->signup_blood_quanhe=$data['quanhe'];
        $signup_blood->signup_blood_cunggioi=$data['quanhecunggioi'];
        $signup_blood->signup_blood_vacxin=$data['vacxin'];
        $signup_blood->signup_blood_vungdich=$data['songtrongvungdich'];
        $signup_blood->signup_blood_bicum=$data['bicum'];
    		$signup_blood->signup_blood_khangsinh=$data['khangsinh'];
    		$signup_blood->signup_blood_chuarang=$data['chuarang'];  
    		$signup_blood->signup_blood_tantat=$data['tantat'];   


        $kinhnguyet='';
        $sinhcon='';
        if(isset($data['kinhnguyet']) && isset($data['sinhcon']))
        {
          $kinhnguyet=$data['kinhnguyet'];
          $sinhcon=$data['sinhcon'];
        } 

        
        $signup_blood->signup_blood_kinhnguyet=$kinhnguyet;   
        $signup_blood->signup_blood_sinhcon=$sinhcon;  
        
    		
    		$signup_blood->signup_blood_status=0;       
        
    		if($data['blood_donation_id']==null)
    		{
    			Session::put('error_blood','B???n ch??a ch???n ?????t hi???n m??u');
          return Redirect::to('/signup-blood');
    		}else
    		{
          $signup_blood_active=SignupBlood::where('users_id',$customer_id)->where('signup_blood_status',1)->get();
          $signup_blood_unactive=SignupBlood::where('users_id',$customer_id)->where('signup_blood_status',0)->get();
          if(count($signup_blood_active)>0 && count($signup_blood_unactive)==0)
          {
              foreach ($signup_blood_active as $key => $value) {
                  $blood_actual=BloodActual::where('signup_blood_id',$value['signup_blood_id'])->first();
                  if($blood_actual)
                  {
                      if($blood_actual->blood_actual_date < Carbon::now()->subDays(120))
                      {
                       $signup_blood->save();
                        return Redirect::to('/notification-sign-up-blood');
                      }else
                      {
                        Session::put('error_blood','B???n ???? hi???n m??u v??o ng??y '.date('d-m-Y', strtotime($blood_actual->blood_actual_date)).' v?? hi???n t???i b???n v???n ch??a th??? th???c hi???n hi???n m??u ti???p t???c. Th???i gian t???i thi???u c???a m???i l???n hi???n m??u l?? 4 Th??ng. Xin c???m ??n !!!');
                        return Redirect::to('/signup-blood');
                      }
                  }else
                  {
                    Session::put('error_blood','B???n ???? ???????c duy???t cho y??u c???u ????ng k?? hi???n m??u. Xin h??y ki???m tra th??ng tin di???n ra ?????t hi???n m??u, v?? mong b???n ?????n ????ng gi???. Xin c???m ??n !!!');
                        return Redirect::to('/signup-blood');
                  }
                  
              } 
              
          }else
          {
              $signup_blood_unactive=SignupBlood::where('users_id',$customer_id)->where('signup_blood_status',0)->get();
              if(count($signup_blood_unactive)>0)
              {
                Session::put('error_blood','B???n ???? th???c hi???n ????ng k?? m???t ?????t hi???n m??u r???i. N???u mu???n ????ng k?? th??m ?????t hi???n m??u, b???n h??y vui l??ng h???y ????ng k?? ?????t hi???n m??u tr?????c ????. Xin c???m ??n !!!');
                return Redirect::to('/signup-blood');
              }else
              {
                $signup_blood->save();
                return Redirect::to('/notification-sign-up-blood');//???????ng d???n
              }
              
          }
    			
    		}     
    }
    public function notification()
    {

   		$customer_id=Session::get('customer_id');
   		$customer_name=Session::get('customer_name');
      $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
    	if($customer_id)
    	{
    		$signup_blood=SignupBlood::where('users_id',$customer_id)->orderBy('signup_blood_id','desc')->get();
        $data=[];
    		foreach($signup_blood as $key => $value)
    		{
    			$blood=BloodDonation::where('blood_donation_id',$value['blood_donation_id'])->first();
    			$blood_name=$blood['blood_donation_name'];
    			$blood_time=date('h:i', strtotime($blood['blood_donation_time'])).'--'.date('d-m-Y', strtotime($blood['blood_start_date']));
    			$blood_status="";
    			if($value['signup_blood_status']==1)
    			{
    				$blood_status="???? Duy???t";
    			}
    			else
    			{
    				$blood_status="Ch??a Duy???t";
    			}
    			$key=$value['signup_blood_id'];
    			$data[$key]=
	    		[
	    			'name'=>$customer_name,
	    			'blood'=>$blood_name,
	    			'time'=>$blood_time,
	    			'status'=>$blood_status,
            'blood_status'=>$value['signup_blood_status'],
	    			'note'=>$value['signup_blood_note']
	    		];
    		}
    		return view('client.signupBlood.notification_status')->with(compact('data','category_news'));
    	}else
    	{
    		return Redirect::to('/login')->send();
    	}
    	
    }
    public function list_signup_blood()
    {
    	$this->check_login();
    	$list_signup_blood=DB::table('tbl_signup_blood')
    	->join('tbl_users','tbl_signup_blood.users_id','=','tbl_users.users_id')
    	->join('tbl_blood_donation','tbl_signup_blood.blood_donation_id','=','tbl_blood_donation.blood_donation_id')
    	->select('tbl_signup_blood.*','tbl_users.users_fullname','tbl_blood_donation.blood_donation_name')
    	->orderBy('tbl_signup_blood.signup_blood_id','desc')->get();
    	return view('admin.signupBlood.index')->with(compact('list_signup_blood'));
    }
    public function show_data(Request $request)
    {
        $signup_blood_id=$request->signup_blood_id;
        $list_signup_blood=DB::table('tbl_signup_blood')
        ->join('tbl_users','tbl_signup_blood.users_id','=','tbl_users.users_id')
        ->where('tbl_signup_blood.signup_blood_id',$signup_blood_id)
        ->select('tbl_signup_blood.*','tbl_users.users_fullname','tbl_users.users_blood')
        ->first();
        
        $landau="";
        $macbenh="";
        $sutcan="";
        $noihach="";
        $phauthuat="";
        $xamminh="";
        $duoctruyenmau=""; $matuy=""; $quanhe="";$quanhecunggioi="";
        $vacxin="";$vungdich="";$bicum="";$khangsinh="";$chuarang="";
        $tantat="";$kinhnguuet="";$sinhcon="";
        
        $fullname=$list_signup_blood->users_fullname;
        $blood_group=$list_signup_blood->users_blood;
        
        if($list_signup_blood->signup_blood_landau==0)
        {
          $landau="Kh??ng";
        }else
        {
          $landau="C??";
        }
        if($list_signup_blood->signup_blood_macbenh==0)
        {
          $macbenh="Kh??ng";
        }else
        {
          $macbenh="C??";
        }
        if($list_signup_blood->signup_blood_sutcan==0)
        {
          $sutcan="Kh??ng";
        }else
        {
          $sutcan="C??";
        }
        if($list_signup_blood->signup_blood_noihach==0)
        {
          $noihach="Kh??ng";
        }else
        {
          $noihach="C??";
        }
        if($list_signup_blood->signup_blood_phauthuat==0)
        {
          $phauthuat="Kh??ng";
        }else
        {
          $phauthuat="C??";
        }
        if($list_signup_blood->signup_blood_xamminh==0)
        {
          $xamminh="Kh??ng";
        }else
        {
          $xamminh="C??";
        }
        if($list_signup_blood->signup_blood_duoctruyenmau==0)
        {
          $duoctruyenmau="Kh??ng";
        }else
        {
          $duoctruyenmau="C??";
        }
        if($list_signup_blood->signup_blood_matuy==0)
        {
          $matuy="Kh??ng";
        }else
        {
          $matuy="C??";
        }
        if($list_signup_blood->signup_blood_quanhe==0)
        {
          $quanhe="Kh??ng";
        }else
        {
          $quanhe="C??";
        }
        if($list_signup_blood->signup_blood_cunggioi==0)
        {
          $quanhecunggioi="Kh??ng";
        }else
        {
          $quanhecunggioi="C??";
        }
        if($list_signup_blood->signup_blood_vacxin==0)
        {
          $vacxin="Kh??ng";
        }else
        {
          $vacxin="C??";
        }
        if($list_signup_blood->signup_blood_vungdich==0)
        {
          $vungdich="Kh??ng";
        }else
        {
          $vungdich="C??";
        }
        if($list_signup_blood->signup_blood_bicum==0)
        {
          $bicum="Kh??ng";
        }else
        {
          $bicum="C??";
        }
        if($list_signup_blood->signup_blood_khangsinh==0)
        {
          $khangsinh="Kh??ng";
        }else
        {
          $khangsinh="C??";
        }
        if($list_signup_blood->signup_blood_chuarang==0)
        {
          $chuarang="Kh??ng";
        }else
        {
          $chuarang="C??";
        }
        if($list_signup_blood->signup_blood_tantat==0)
        {
          $tantat="Kh??ng";
        }else
        {
          $tantat="C??";
        }

        if($list_signup_blood->signup_blood_kinhnguyet !=null)
        {
            if($list_signup_blood->signup_blood_kinhnguyet==0)
            {
              $kinhnguuet="Kh??ng";
            }else
            {
              $kinhnguuet="C??";
            }
        }else
        {
            $kinhnguuet="";
        }
        

        if($list_signup_blood->signup_blood_sinhcon != null)
        {
            if($list_signup_blood->signup_blood_sinhcon==0)
            {
              $sinhcon="Kh??ng";
            }else
            {
              $sinhcon="C??";
            }
        }else
        {
            $sinhcon="";
        }
        

        


        $key=$list_signup_blood->signup_blood_id;
        $data[$key]=
        [
          'landau'=>$landau,'macbenh'=>$macbenh,'sutcan'=>$sutcan,'noihach'=>$noihach,
          'phauthuat'=>$phauthuat,'xamminh'=>$xamminh,'duoctruyenmau'=>$duoctruyenmau,
          'matuy'=>$matuy,'quanhe'=>$quanhe,'cunggioi'=>$quanhecunggioi,'vacxin'=>$vacxin,
          'vungdich'=>$vungdich,'bicum'=>$bicum,'khangsinh'=>$khangsinh,'chuarang'=>$chuarang,
          'tantat'=>$tantat,'kinhnguyet'=>$kinhnguuet,'sinhcon'=>$sinhcon

        ];
        $dataname[]=
        [
          'fullname'=>$fullname,
          'users_blood'=>$blood_group
        ];
        return response()->json([
            'error'    => false,
            'response'=>$data,
            'responses'=>$dataname,
            'messages' => "L??u th??nh c??ng",
        ], 200);

    }
    public function active_signup_blood(Request $request)
    {
        $signup_blood_id=$request->signup_blood_id;
        $signup_blood=SignupBlood::find($signup_blood_id);
        $signup_blood->signup_blood_status=1;
        $signup_blood->save();
    }
    public function reply_note_signup_blood(Request $request)
    {
        $request->validate([
            'sign_note'=>'required',
        ]);

        $signup_blood_id=$request->signup_blood_id;
        $signup_blood_note=$request->sign_note;

        $signup_blood=SignupBlood::find($signup_blood_id);
        $signup_blood->signup_blood_note=$signup_blood_note;

        $signup_blood->save();
        
        
    }
    public function delete_signup_blood(Request $request)
    {
        $signup_blood_id=$request->signup_blood_id;
        $signup_blood=SignupBlood::find($signup_blood_id);
        $signup_blood->delete();
    }
    public function history_blood()
    {
      $customer_id=Session::get('customer_id');
      $customer_name=Session::get('customer_name');
      if($customer_id)
      {
          $sign_blood=SignupBlood::where('users_id', $customer_id)->where('signup_blood_status',1)->get();
          $data=[];
          foreach ($sign_blood as $key => $value) {
            $blood_actual=BloodActual::where('signup_blood_id',$value['signup_blood_id'])->first();
            $blood_donation=BloodDonation::where('blood_donation_id',$value['blood_donation_id'])->first();
              $data[]=
              [
                'blood_name'=>$blood_donation->blood_donation_name,
                'blood_group'=>$blood_actual->blood_actual_group,
                'blood_unit'=>$blood_actual->blood_actual_unit,
                'blood_actual_date'=>$blood_actual->blood_actual_date,
                'blood_donation_place'=>$blood_donation->blood_donation_place
              ];
          }
          $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
          View::share('history_blood',$data);
          return view('client.historyBlood.historyBlood')->with(compact('category_news'));
      }else
      {
          return Redirect::to('/login')->send();
      }
      
    }
    public function blood_donation_schedule()
    {
      $customer_id=Session::get('customer_id');
      if($customer_id)
      {
        $category_news=CategoryNews::orderBy('category_news_id','desc')->where('category_news_status',1)->get();
        $blood_donation=BloodDonation::orderBy('blood_donation_id','desc')->where('blood_status',1)->get();
        return view('client.signupBlood.blood_donation_schedule')->with(compact('category_news','blood_donation'));
      }else
      {
        return Redirect::to('/login')->send();
      }
    }
}

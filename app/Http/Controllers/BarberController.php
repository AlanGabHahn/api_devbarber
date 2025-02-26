<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    User,
    UserAppointment,
    Barber,
    BarberPhoto,
    BarberService,
    BarberTestimonial,
    BarberAvailability
};

class BarberController extends Controller
{

    private $logged;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->logged = auth()->user();
    }

    public function createRandom() {
        $array = ['error' => ''];

        for($q=0;$q<15;$q++) {
            $names = ['Bonieky', 'Paulo', 'Pedro', 'Amanda', 'Leticia', 'Gabriel', 'Ronaldo'];
            $lastnames = ['Silva', 'Lacerda', 'Diniz', 'Alvaro', 'Sousa', 'Gomes'];

            $servicos = ['Corte', 'Pintura', 'Aparação', 'Enfeite'];
            $servicos2 = ['Cabelo', 'Unha', 'Pernas', 'Sobrancelhas'];

            $depos = [
                'Maecenas ullamcorper mi a justo egestas ultrices quis eget lacus.',
                'Fusce malesuada justo in maximus auctor. In quis enim in.',
                'Aliquam dapibus id dolor non auctor. Morbi vehicula nec ex.',
                'Sed pulvinar, neque sed blandit fermentum, dui mi sollicitudin turpis.',
                'Nam luctus accumsan enim, a finibus sapien rhoncus fermentum. Praesent.'
            ];

            $newBarber = new Barber();
            $newBarber->name = $names[rand(0, count($names)-1)].' '.$lastnames[rand(0, count($lastnames)-1)];
            $newBarber->avatar = rand(1, 4).'.png';
            $newBarber->stars = rand(2, 4).'.'.rand(0, 9);
            $newBarber->latitude = '-23.5'.rand(0,9).'30907';
            $newBarber->longitude = '-46.6'.rand(0,9).'82795';
            $newBarber->save();

            $ns = rand(3, 6);
            for($w=0;$w<4;$w++) {
                $newBarberPhoto = new BarberPhoto();
                $newBarberPhoto->barber_id = $newBarber->id;
                $newBarberPhoto->url = rand(1, 5).'.png';
                $newBarberPhoto->save();
            }

            for($w=0;$w<$ns;$w++) {
                $newBarberService = new BarberService();
                $newBarberService->barber_id = $newBarber->id;
                $newBarberService->name = $servicos[rand(0, count($servicos)-1)].' de '.$servicos2[rand(0, count($servicos2)-1)];
                $newBarberService->price = rand(1, 99).'.'.rand(0, 100);
                $newBarberService->save();
            }

            for($w=0;$w<3;$w++) {
                $newBarberTestimonial = new BarberTestimonial();
                $newBarberTestimonial->barber_id = $newBarber->id;
                $newBarberTestimonial->name = $names[rand(0, count($names)-1)].' '.$lastnames[rand(0, count($lastnames)-1)];
                $newBarberTestimonial->rate = rand(2, 4).'.'.rand(0, 9);
                $newBarberTestimonial->body = $depos[rand(0, count($depos)-1)];
                $newBarberTestimonial->save();
            }

            for($e=0;$e<4;$e++) {
                $rAdd = rand(7, 10);
                $hours = [];
                for($r=0;$r<8;$r++) {
                    $time = $r + $rAdd;
                    if($time < 10) {
                        $time = '0'.$time;
                    }
                    $hours[] = $time.':00';
                }
                $newBarberAvail = new BarberAvailability();
                $newBarberAvail->barber_id = $newBarber->id;
                $newBarberAvail->weekday = $e;
                $newBarberAvail->hours = implode(',', $hours);
                $newBarberAvail->save();
            }

        }
        return $array;
    }

    public function list(Request $request)
    {
        $array = ['error' => ''];

        $lat = $request->input('lat');
        $lng = $request->input('long');
        $city = $request->input('city');

        $offset = $request->input('offset');
        if (!$offset) {
            $offset = 0;
        }

        if (!empty($city)) {
            $result = $this->searchGeo($city);

            if (count($result['results']) > 0) {
                $lat = $result['results'][0]['geometry']['location']['lat'];
                $lng = $result['results'][0]['geometry']['location']['lng'];
            }
        } elseif (!empty($lat) && !empty($lng)) {
            $result = $lat . ',' . $lng;

            if (count($result['results']) > 0) {
                $city = $result['results'][0]['formatted_address'];
            }
        } else {
            $lat = '-23.5630907';
            $lng = '-46.6682795';
            $city = 'São Paulo';
        }

        $barbers = Barber::select(Barber::raw('*, SQRT(
                                                POW(69.1 * (latitude - '. $lat. '), 2) +
                                                POW(69.1 * ('. $lng.' - longitude) * COS(latitude / 57.3), 2)) AS distance'
                                            ))
                                ->havingRaw('distance < ?', [10])
                                ->orderBy('distance', 'asc')
                                ->offset($offset)
                                ->limit(5)
                                ->get();

        foreach ($barbers as $bkey => $bvalue) {
            $barbers[$bkey]['avatar'] = url('media/avatars/'.$barbers[$bvalue]['avatar']);
        }

        $array['data'] = $barbers;
        $array['loc'] = 'São Paulo';

        return $array;
    }

    public function one($id)
    {
        $array = ['error' => ''];

        $barber = Barber::find($id);

        if ($barber) {
            $barber['avatar'] = url('media/avatars/'. $barber['avatar']);
            $barber['favorited'] = false;
            $barber['photos'] = [];
            $barber['services'] = [];
            $barber['testimonials'] = [];
            $barber['available'] = [];

            $barber['photos'] = BarberPhoto::select(['id', 'url'])
                                            ->where('barber_id', $barber->id)
                                            ->get();
            foreach ($barber['photos'] as $bpkey => $bpvalue) {
                $barber['photos'][$bpkey]['url'] = url('media/uploads/'. $barber['photos'][$bpvalue]['url']);
            }

            $barber['services'] = BarberService::select(['id', 'name', 'price'])
                                                ->where('barber_id', $barber->id)
                                                ->get();

            $barber['testimonials'] = BarberTestimonial::select(['id', 'name', 'rate', 'body'])
                                                        ->where('barber_id', $barber->id)
                                                        ->get();
            
            $availability = [];

            $avails = BarberAvailability::select(['id', 'weekday', 'hours'])
                                                        ->where('barber_id', $barber->id)
                                                        ->get();
            $avails_weekdays = [];
            foreach ($avails as $item) {
                $avails_weekdays[$item['weekday']] = explode(',', $item['hours']);
            }

            $appointments = [];

            $user_appointments = UserAppointment::where('barber_id', $barber->id) 
                                                ->whereBetween('ap_datetime', [
                                                    date('Y-m-d'). ' 00:00:00', 
                                                    date('Y-m-d', strtotime('+20 days')).' 23:59:59'
                                                ])
                                                ->get();
            foreach ($user_appointments as $item) {
                $appointments[] = $item['ap_datetime'];
            }


            $barber['available'] = $availability;

            $array['data'] = $barber;
        } else  {
            $array['error'] = 'Barbeiro não encontrado';
            return $array;
        }

        return $array;
    }

    private function searchGeo($address)
    {
        $key = env('MAPS_KEY', null);

        $address = urlencode($address);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}

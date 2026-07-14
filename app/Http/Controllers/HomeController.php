<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }

        return abort(404);
    }

    public function awards()
    {
        $parentId = parentId();
        $user = Auth::user();
        $goal = 10000.0;
        $revenueTotal = 0.0;

        if (Schema::hasTable('invoice_payments') && Schema::hasTable('invoices')) {
            $revenueTotal = (float) InvoicePayment::whereHas('invoice', fn ($q) => $q->where('parent_id', $parentId))
                ->sum('amount');
        } elseif (Schema::hasTable('invoices')) {
            $revenueTotal = (float) Invoice::where('parent_id', $parentId)->sum('paid_amount');
        }

        $progress = $goal > 0 ? min(100, (int) round(($revenueTotal / $goal) * 100)) : 0;
        $remaining = max(0, $goal - $revenueTotal);
        $userAttributes = $user?->getAttributes() ?? [];
        $fieldValue = function (array $fields) use ($userAttributes) {
            foreach ($fields as $field) {
                if (array_key_exists($field, $userAttributes)) {
                    return $userAttributes[$field];
                }
            }

            return null;
        };
        $profileChecklist = [
            ['label' => 'Nome', 'complete' => filled($fieldValue(['name']))],
            ['label' => 'CPF', 'complete' => filled($fieldValue(['cpf', 'document', 'document_number']))],
            ['label' => 'WhatsApp', 'complete' => filled($fieldValue(['whatsapp', 'phone', 'mobile_phone']))],
            ['label' => 'Endereço', 'complete' => filled($fieldValue(['address', 'endereco']))],
        ];

        return view('mgteam.awards', compact('revenueTotal', 'goal', 'progress', 'remaining', 'profileChecklist'));
    }

    public function root()
    {
        return app(DashboardController::class)->index();
    }

    /* Language Translation */
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();

            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::findOrFail($id);

        abort_unless($user->id === Auth::id(), 403);

        $profileName = trim(collect([
            $request->get('first_name'),
            $request->get('last_name'),
        ])->filter()->implode(' '));

        $user->name = $profileName !== '' ? $profileName : $request->get('name', $user->name);
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time().'.'.$avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar = $avatarName;
        }

        $user->save();
        if ($user) {
            Session::flash('message', 'Perfil atualizado com sucesso!');
            Session::flash('alert-class', 'alert-success');

            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');

            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        abort_unless((int) $id === Auth::id(), 403);

        if (! (Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => 'Your Current password does not matches with the password you provided. Please try again.',
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');

                return response()->json([
                    'isSuccess' => true,
                    'Message' => 'Password updated successfully!',
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');

                return response()->json([
                    'isSuccess' => true,
                    'Message' => 'Something went wrong!',
                ], 200); // Status code here
            }
        }
    }
}

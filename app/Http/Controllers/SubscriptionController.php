<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Display subscription plans
     */
    public function index(): View
    {
        $parentId = parentId();
        $plans = SubscriptionPlan::where('parent_id', $parentId)->active()->get();
        $gateways = PaymentGatewayFactory::getAvailableGateways();

        return view('subscriptions.index', compact('plans', 'gateways'));
    }

    /**
     * Show subscription checkout
     */
    public function checkout(Request $request, SubscriptionPlan $plan): View
    {
        // Ensure user has a member profile
        $member = Member::where('user_id', auth()->id())->firstOrFail();
        $gateways = PaymentGatewayFactory::getAvailableGateways();

        return view('subscriptions.checkout', compact('plan', 'member', 'gateways'));
    }

    /**
     * Process subscription purchase
     */
    public function purchase(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'payment_gateway' => 'required|in:stripe,paypal',
        ]);

        $member = Member::where('user_id', auth()->id())->firstOrFail();

        // Create subscription record
        $subscription = Subscription::create([
            'member_id' => $member->id,
            'subscription_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'trial_end_date' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
            'status' => $plan->trial_days > 0 ? 'trial' : 'active',
            'payment_gateway' => $validated['payment_gateway'],
        ]);

        // Create payment session with selected gateway
        try {
            $gateway = PaymentGatewayFactory::create($validated['payment_gateway']);
            $session = $gateway->createPaymentSession($subscription);

            return redirect($session['url']);
        } catch (\Exception $e) {
            $subscription->delete();

            return back()->with('error', 'Payment failed: '.$e->getMessage());
        }
    }

    /**
     * Payment success callback
     */
    public function success(Subscription $subscription): View
    {
        return view('subscriptions.success', compact('subscription'));
    }

    /**
     * PayPal success callback
     */
    public function paypalSuccess(Request $request, Subscription $subscription)
    {
        try {
            $gateway = PaymentGatewayFactory::create('paypal');
            $gateway->handleWebhook([
                'paymentId' => $request->paymentId,
                'PayerID' => $request->PayerID,
            ]);

            return redirect()->route('subscriptions.success', $subscription);
        } catch (\Exception $e) {
            return redirect()->route('subscriptions.cancel', $subscription)
                ->with('error', 'Payment verification failed');
        }
    }

    /**
     * Payment cancelled callback
     */
    public function cancel(Subscription $subscription): View
    {
        // Delete pending subscription if payment was cancelled
        if ($subscription->status === 'trial' && ! $subscription->transactions()->completed()->count()) {
            $subscription->delete();
        }

        return view('subscriptions.cancel');
    }

    /**
     * Show member's subscription
     */
    public function mySubscription(): View
    {
        $user = auth()->user();
        $parentId = parentId();
        $member = Member::where('user_id', $user->id)->first();
        $subscription = $member?->subscriptions()->with('plan', 'transactions')->latest()->first();
        $plans = SubscriptionPlan::where('parent_id', $parentId)
            ->active()
            ->orderByDesc('is_featured')
            ->orderBy('price')
            ->get();
        $membersQuery = Member::where('parent_id', $parentId);
        $memberStats = [
            'active' => (clone $membersQuery)->active()->count(),
            'total' => (clone $membersQuery)->count(),
            'expired' => (clone $membersQuery)->expired()->count(),
        ];

        return view('subscriptions.my-subscription', compact('member', 'memberStats', 'plans', 'subscription', 'user'));
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Subscription $subscription): RedirectResponse
    {
        $member = Member::where('user_id', auth()->id())->firstOrFail();

        if ($subscription->member_id !== $member->id) {
            abort(403);
        }

        try {
            if ($subscription->gateway_subscription_id) {
                $gateway = PaymentGatewayFactory::create($subscription->payment_gateway);
                $gateway->cancelSubscription($subscription->gateway_subscription_id);
            }

            $subscription->cancel();

            return back()->with('success', 'Subscription cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Cancellation failed: '.$e->getMessage());
        }
    }
}

import { formatMoney } from '@/lib/format';
import { Head, router } from '@inertiajs/react';
import { FlaskConical } from 'lucide-react';

/** Local-only stand-in for the payment provider's hosted page. */
export default function Sandbox({ transaction }: { transaction: { reference: string; amount: number; currency: string; status: string } }) {
    const simulate = (outcome: 'success' | 'failed') => router.post(route('payments.sandbox.simulate', transaction.reference), { outcome });

    return (
        <div className="flex min-h-screen items-center justify-center bg-neutral-100 p-6">
            <Head title="Sandbox payment" />
            <div className="w-full max-w-md rounded-2xl border-2 border-dashed border-amber-400 bg-white p-8 text-center shadow">
                <FlaskConical className="mx-auto size-10 text-amber-500" />
                <h1 className="mt-3 font-sans text-xl font-bold">Payment sandbox</h1>
                <p className="mt-1 text-sm text-neutral-600">Development only — no real payment provider, no money taken.</p>
                <p className="mt-6 text-3xl font-bold">{formatMoney(transaction.amount, transaction.currency)}</p>
                <p className="text-xs text-neutral-500">{transaction.reference}</p>
                <div className="mt-8 grid gap-3">
                    <button
                        onClick={() => simulate('success')}
                        className="rounded-md bg-emerald-600 px-4 py-3 font-medium text-white hover:bg-emerald-700"
                    >
                        Simulate successful payment
                    </button>
                    <button
                        onClick={() => simulate('failed')}
                        className="rounded-md border border-neutral-300 px-4 py-3 font-medium hover:bg-neutral-50"
                    >
                        Simulate failed payment
                    </button>
                </div>
            </div>
        </div>
    );
}

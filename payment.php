//PaymentResource
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('payment_method')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('paid_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')->sortable(),
                Tables\Columns\TextColumn::make('amount')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->searchable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}


//ClientAuth
    <?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class ClientAuth
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next): Response
        {
            $token = $request->bearerToken();
            $client = Client::where('api_token', $token)->first();
            if (!$client){
                return response()->json([
                    'message' => 'Unathorized'
                ], 401);
            }
            $request->merge(['authenticated_client' => $client]);
            return $next($request);
        }
    }

//EncryptionHelper
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{
    /**
     * Encrypt data using a custom key.
     */
    public static function encrypt($data)
    {
        $key = env('KEY_ENCRYPT', 'defaultkey');  // Use the key from the .env file
        return Crypt::encryptString($data, false);  // Encrypt data with the key
    }

    /**
     * Decrypt data using a custom key.
     */
    public static function decrypt($encryptedData)
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            return 'Decryption failed: ' . $e->getMessage();
        }
    }
}

//EncryptPaymentData
<?php

namespace App\Services;

use App\Helpers\EncryptionHelper;
use App\Models\Payment;

class EncryptPaymentData
{
    /**
     * Encrypt sensitive fields in a Payment record.
     */
    public static function encryptPayment(Payment $payment): array
    {
        return [
            'user_id'        => $payment->user_id,
            'amount'         => EncryptionHelper::encrypt($payment->amount),
            'payment_method' => EncryptionHelper::encrypt($payment->payment_method),
            'status'         => $payment->status,
            'paid_at'        => $payment->paid_at,
            'created_at'     => $payment->created_at,
        ];
    }

    /**
     * Decrypt sensitive fields in an encrypted Payment array.
     */
    public static function decryptPayment(array $encryptedData): array
    {
        return [
            'user_id'        => $encryptedData['user_id'],
            'amount'         => EncryptionHelper::decrypt($encryptedData['amount']),
            'payment_method' => EncryptionHelper::decrypt($encryptedData['payment_method']),
            'status'         => $encryptedData['status'],
            'paid_at'        => $encryptedData['paid_at'],
            'created_at'     => $encryptedData['created_at'],
        ];
    }
}

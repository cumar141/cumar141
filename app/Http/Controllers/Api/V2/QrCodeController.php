<?php



namespace App\Http\Controllers\Api\V2;



use App\Http\Controllers\Controller;

use App\Http\Requests\Api\V2\QrCode\{

    QrCodeExpressRequest,

    QrCodePaymentRequest,

    QrCodePaymentSubmitRequest,

    QrCodeRequest

};



use App\Services\QrCodeService;

use Exception;



class QrCodeController extends Controller

{



    /**

     * QrCodeService

     *

     * @var QrCodeService

     */

    private $service;



    public function __construct(QrCodeService $service)

    {

        $this->service = $service;

    }



    /**

     * Method getQrSecret

     *

     * @return void

     */

    public function getQrCode()

    {

        try {

            $secret = $this->service->getQrSecret();

            return $this->successResponse($secret);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }

    }

    

       /**

     * Method getQrImage

     *

     * @return void

     */

    public function getQrImage()

    {

        try {

            $secret = $this->service->getQrImage();

            return $this->successResponse($secret);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }

    }



    /**

     * Method addOrUpdateQrCode

     *

     * @return void

     */

    public function addOrUpdateQrCode()

    {

        try {

            $qrCode = $this->service->addUpdateQrSecret();

            return $this->successResponse($qrCode);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }



    }



    public function sendRequestQrOperation(QrCodeRequest $request)

    {

        try {

            $qrDetails = $this->service->userSendRequestDetails($request->secret_text);

            return $this->successResponse($qrDetails);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }

    }



    public function merchantQrOperation(QrCodeRequest $request)

    {

        try {

            $qrDetails = $this->service->merchantPaymentDetails($request->secret_text);

            return $this->successResponse($qrDetails);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }

    }

    

    public function merchantQrOperationByNumber(QrCodeRequest $request)

    {

        try {

            $qrDetails = $this->service->merchantPaymentDetailsByNumber($request->merchant_number);

            return $this->successResponse($qrDetails);

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }

    }



    public function merchantPaymentSubmit(QrCodePaymentSubmitRequest $request)

    {

        try {

            extract($request->all());

           

            return $this->okResponse(

                $this->service->qrPaymentSubmit(

                    $merchant_user_id, $merchant_id, $currency_id, $amount, $fee, $note

                )

            );



        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }



    }



    public function merchantPaymentReview(QrCodeExpressRequest $request)

    {

        try {

            extract($request->all());

            return $this->successResponse(

                $this->service->merchantPaymentReview(

                    $merchant_id, $currency_code, $amount

                )

            );

        } catch (Exception $e) {

            return $this->unprocessableResponse([], $e->getMessage());

        }



    }



}


<?php

namespace App\Http\Controllers\Api\Platform;

use App\Exports\PrevalidationsExport;
use App\Http\Requests\Api\Platform\Prevalidations\RedeemPrevalidationRequest;
use App\Http\Requests\Api\Platform\Prevalidations\SearchPrevalidationsRequest;
use App\Http\Requests\Api\Platform\Prevalidations\StorePrevalidationsRequest;
use App\Http\Requests\Api\Platform\Prevalidations\UploadPrevalidationsRequest;
use App\Http\Resources\PrevalidationResource;
use App\Models\Fund;
use App\Models\Prevalidation;
use App\Traits\ThrottleWithMeta;
use App\Http\Controllers\Controller;

class PrevalidationController extends Controller
{
    use ThrottleWithMeta;

    private $recordRepo;
    private $maxAttempts = 3;
    private $decayMinutes = 180;

    /**
     * RecordCategoryController constructor.
     */
    public function __construct() {
        $this->recordRepo = resolve('forus.services.record');
        $this->maxAttempts = env('ACTIVATION_CODE_ATTEMPTS', $this->maxAttempts);
        $this->decayMinutes = env('ACTIVATION_CODE_DECAY', $this->decayMinutes);
    }

    /**
     * @param StorePrevalidationsRequest $request
     * @return PrevalidationResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(
        StorePrevalidationsRequest $request
    ) {
        $this->authorize('store', Prevalidation::class);

        $prevalidations = Prevalidation::storePrevalidations(
            Fund::find($request->input('fund_id')),
            [$request->input('data')]
        );

        return new PrevalidationResource($prevalidations[0]);
    }

    /**
     * @param UploadPrevalidationsRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function storeCollection(
        UploadPrevalidationsRequest $request
    ) {
        $this->authorize('store', Prevalidation::class);

        $prevalidations = Prevalidation::storePrevalidations(
            Fund::find($request->input('fund_id')),
            $request->input('data')
        );

        return PrevalidationResource::collection($prevalidations);
    }

    /**
     * @param SearchPrevalidationsRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(
        SearchPrevalidationsRequest $request
    ) {
        $this->authorize('viewAny', Prevalidation::class);

        return PrevalidationResource::collection(Prevalidation::search(
            $request
        )->with('prevalidation_records.record_type')->paginate());
    }

    /**
     * @param SearchPrevalidationsRequest $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer
     */
    public function export(
        SearchPrevalidationsRequest $request
    ) {
        $this->authorize('viewAny', Prevalidation::class);

        return resolve('excel')->download(
            new PrevalidationsExport($request),
            date('Y-m-d H:i:s') . '.xls'
        );
    }

    /**
     * Redeem prevalidation.
     *
     * @param RedeemPrevalidationRequest $request
     * @param Prevalidation|null $prevalidation
     * @return PrevalidationResource
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Exception
     */
    public function redeem(
        RedeemPrevalidationRequest $request,
        Prevalidation $prevalidation = null
    ) {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->responseWithThrottleMeta('to_many_attempts', $request);
        }

        $this->incrementLoginAttempts($request);

        if (!$prevalidation || !$prevalidation->exists()) {
            $this->responseWithThrottleMeta('not_found', $request, 404);
        }

        $this->authorize('redeem', $prevalidation);
        $this->clearLoginAttempts($request);

        $prevalidation->assignToIdentity(auth_address());

        return new PrevalidationResource($prevalidation);
    }
}

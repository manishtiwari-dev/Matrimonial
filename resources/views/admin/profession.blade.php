@extends('admin.layouts.app')

@section('panel')
    <div class="row">

        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profession as $prf)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>
                                            <span class="fw-bold">{{ __($prf->name) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap justify-content-end gap-1">
                                                <button class="btn btn-outline--primary btn-sm cuModalBtn" data-modal_title="@lang('Update Profession')" data-resource="{{ $prf }}" type="button">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                <button class="btn btn-outline--danger btn-sm confirmationBtn" data-action="{{ route('admin.profession.delete', $prf->id) }}" data-question="@lang('Are you sure, you want to delete this religion?')" type="button">
                                                    <i class="las la-trash-alt"></i>@lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ $emptyMessage }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>
    <x-confirmation-modal />


    {{-- Add METHOD MODAL --}}
    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.profession.save') }}" method="POST"  class="needs-validation"  novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input class="form-control" name="name" placeholder="@lang('Enter new profession')" required type="text">
                            <div class="invalid-feedback">
                              please enter a name
                              </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add Profession')" type="button">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush




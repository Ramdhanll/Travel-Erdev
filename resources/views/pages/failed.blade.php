@extends('layouts.success')
@section('titile','Transaction Success')

@section('content')
    <main>
        <section class="section-success d-flex align-item-center">
            <div class="col text-center">
                <img src=" {{url('frontend/images/IC-success.png')}} " class="mt-5">
                <h1>Oops! </h1>
                <p>
                    Your transaction is failed
                    <br>
                    please contact our representative if this problem occurs
                </p>
                <a href=" {{ url('/') }} " class="btn btn-home-page mt-3 px-5">
                    Home Page
                </a>
            </div>
        </section>
    </main>
@endsection
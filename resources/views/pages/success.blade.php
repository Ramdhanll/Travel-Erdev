@extends('layouts.success')
@section('titile','Transaction Success')

@section('content')
    <main>
        <section class="section-success d-flex align-item-center">
            <div class="col text-center">
                <img src=" {{url('frontend/images/IC-success.png')}} " class="mt-5">
                <h1>Yay! Success </h1>
                <p>
                    We've sent you email for trip instruction
                    <br>
                    please read it as well
                </p>
                <a href=" {{ url('/') }} " class="btn btn-home-page mt-3 px-5">
                    Home Page
                </a>
            </div>
        </section>
    </main>
@endsection
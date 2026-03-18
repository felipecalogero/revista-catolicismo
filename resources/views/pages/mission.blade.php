@extends('layouts.app')

@section('title', 'Nossa Missão - Revista Catolicismo')
@section('description', 'Nossa Missão - Revista Catolicismo')

@section('content')
<div class="relative min-h-screen bg-[#f5f0e6] py-12">
    <img
        src="{{ asset('img/textura.jpeg') }}"
        alt=""
        class="absolute inset-0 w-full h-full object-cover"
    >

    <div class="relative z-10 container mx-auto px-4 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8 md:p-12">
            <div class="mb-8 pb-6 border-b-2 border-red-800">
                <nav class="text-sm text-gray-600 mb-4">
                    <a href="{{ route('home') }}" class="hover:text-red-800 transition-colors">Início</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 font-medium">Nossa Missão</span>
                </nav>
                <h1 class="text-4xl font-bold text-gray-900 font-serif mb-2">Nossa Missão</h1>
                <p class="text-gray-600">O propósito que orienta a Revista Catolicismo</p>
            </div>

            <div class="prose prose-lg max-w-none font-serif">
                <p class="text-gray-800 text-xl md:text-2xl leading-relaxed md:leading-relaxed tracking-wide">
                    “Ser um órgão de formação doutrinária e de combate de ideias que, à luz do Magistério tradicional, defende a fé católica e a doutrina social da Igreja, contribuindo para a preservação e a restauração da Civilização Cristã.”
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

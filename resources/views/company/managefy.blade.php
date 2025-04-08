@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            <nav aria-label="breadcrumb">
               <ol class="breadcrumb m-0 py-4 px-2  ">
                  <li class="breadcrumb-item">
                     <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                  </li>
                  <li class="breadcrumb-item">
                     <a class="fw-bold font-heading font-12 text-decoration-none" href="#">Company Finacial Year</a>
                  </li>
               </ol>
            </nav>
            <form class="bg-white px-4 py-3" method="POST" action="{{ route('change-financial-year') }}">
               @csrf
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Financial Year</label>
                     <select class="form-select" id="current_finacial_year" name="current_finacial_year" >
                        <?php 
                        $fy = explode("-",$current_finacial_year);
                        $y = $fy[0];
                        while($y<=date('y')){
                           $y1 = $y+1;
                           ?>
                           <option value="<?php echo $y."-".$y1;?>" <?php if($y."-".$y1==$default_fy){ echo 'selected';}?>><?php echo $y."-".$y1;?></option>
                           <?php
                           $y++;
                        }
                        ?>
                     </select>
                  </div>
               </div>
               <div class="d-flex justify-content-between align-items-center">
                  <button type="submit" class="btn  btn-small-primary mb-4">SAVE</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
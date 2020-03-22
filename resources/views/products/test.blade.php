<div class="album py-5 bg-light">
    <div class="container">

      <div class="row">
        <div class="col-md-4" style="background-color: #C0D9AF;">
          <div class="card mb-4 box-shadow" style=" background-color: #DCF4CC;">
            <img class="card-img-top" src="/gallery/{{$product->thumbnail_path}}" >
            <div class="card-body">
              <h5 class="title"> <a href="/products/{{$product->id}}">{{$product->name}}</a></h5>
              <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$product->selling_Price}}</p>
              <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                </div>
                <small class="text-muted">SOLD</small>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
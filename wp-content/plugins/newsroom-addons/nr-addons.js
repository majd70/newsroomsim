\
jQuery(function($){
  // ---- 1) Create Content form: file input inject (fallback, যদি টেমপ্লেটে না থাকে) ----
  const $form = $('form#create-content, form.newsroom-create, form#newsroom-create');
  if($form.length && $form.find('input[name="nr_photo"]').length===0){
    $form.attr('enctype','multipart/form-data');
    const $target = $form.find('.form-actions,.submit-row').first();
    const html = '<p class="nr-photo-field"><label>Photo: <input type="file" name="nr_photo" accept="image/*"></label></p>';
    if($target.length){ $target.before(html); } else { $form.append(html); }
  }

  // ---- 2) Edit (prompt-based quick edit) ----
  $(document).on('click','.nr-edit',function(e){
    e.preventDefault();
    const id = $(this).data('id');
    const title = prompt('New title?');
    const content = prompt('New content?');
    if(title===null && content===null) return;
    $.post(nrAjax.url, {action:'nr_edit_post', id, title, content, _wpnonce:nrAjax.nonce}, function(res){
      if(res && res.success){ location.reload(); }
      else { alert(res && res.data ? res.data : 'Failed'); }
    });
  });

  // ---- 3) Delete ----
  $(document).on('click','.nr-del',function(e){
    e.preventDefault();
    if(!confirm('Delete this post?')) return;
    const id = $(this).data('id');
    $.post(nrAjax.url, {action:'nr_delete_post', id, _wpnonce:nrAjax.nonce}, function(res){
      if(res && res.success){
        $('#post-'+id).remove();
      } else {
        alert(res && res.data ? res.data : 'Failed');
      }
    });
  });

  // ---- 4) Link Preview: URL ইনপুট চিহ্নিত করে লাইভ প্রিভিউ ----
  const $url = $('#nr-link-input, #news-url, input[name="news_url"]');
  if($url.length){
    const $box = $('<div id="nr-link-preview-box"></div>').insertAfter($url.last());
    $url.on('change blur', function(){
      const v = $(this).val();
      if(!v) return $box.empty();
      $.post(nrAjax.url, {action:'nr_link_preview', url:v}, function(res){
        if(res && res.success){
          const d = res.data;
          $box.html(
            `<div class="nr-link-card">
               ${d.image?`<img src="${d.image}" alt="">`:''}
               <div class="nr-link-meta">
                 <strong>${d.title||'Link Preview'}</strong>
                 <p>${d.description||''}</p>
               </div>
             </div>`
          );
        }else{
          $box.empty();
        }
      });
    });
  }
});

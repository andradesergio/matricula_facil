from django.urls import path
from . import views

urlpatterns = [
    path('portal/matricula/', views.matricula, name='matricula'),  
    path('portal/acesso/', views.login_custom, name='login_custom'),
    path('portal/novo-estudante/', views.cadastro, name='cadastro'),
    path('', views.home, name='home'),  
]
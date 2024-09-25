<?php
class Autentifikasi extends CI_Controller
{
    public function index()
    {
        // Jika sudah login, maka tidak bisa mengakses halaman login dan akan diarahkan ke halaman user
        if ($this->session->userdata('email')) {
            redirect('user');
        }

        // Validasi form untuk input email
        $this->form_validation->set_rules('email', 'Alamat Email', 'required|trim|valid_email', [
            'required' => 'Email Harus diisi!',
            'valid_email' => 'Email Tidak Benar!'
        ]);

        // Validasi form untuk input password
        $this->form_validation->set_rules('password', 'Password', 'required|trim', [
            'required' => 'Password Harus diisi!'
        ]);

        // Jika validasi gagal, kembalikan ke halaman login
        if ($this->form_validation->run() == false) {
            $data['judul'] = 'Login';
            $data['user'] = '';

            // Mengirim variabel judul ke view aute_header
            $this->load->view('templates/aute_header', $data);
            $this->load->view('autentifikasi/login');
            $this->load->view('templates/aute_footer');
        } else {
            // Jika validasi berhasil, proses login
            $this->_login();
        }
    }

    private function _login()
    {
        $email = htmlspecialchars($this->input->post('email', true));
        $password = $this->input->post('password', true);

        // Cek apakah email ada di database
        $user = $this->ModelUser->cekData(['email' => $email])->row_array();

        // Jika user ditemukan
        if ($user) {
            // Jika user sudah aktif
            if ($user['is_active'] == 1) {
                // Cek password
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);

                    // Jika user adalah admin
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        // Jika foto profil masih default
                        if ($user['image'] == 'default.jpg') {
                            $this->session->set_flashdata('pesan', '<div class="alert alert-info alert-message" role="alert">Silahkan ubah profil Anda untuk mengganti foto profil.</div>');
                        }
                        redirect('user');
                    }
                } else {
                    // Password salah
                    $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">Password salah!</div>');
                    redirect('autentifikasi');
                }
            } else {
                // User belum diaktifasi
                $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">User belum diaktifasi!</div>');
                redirect('autentifikasi');
            }
        } else {
            // Email tidak terdaftar
            $this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-message" role="alert">Email tidak terdaftar!</div>');
            redirect('autentifikasi');
        }
    }
}

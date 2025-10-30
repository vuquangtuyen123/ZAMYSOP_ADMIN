
```
ZAMYSOP_ADMIN/
├── Model_ML/
│   ├── train.py             # Script huấn luyện
│   ├── predict.py           # Script dự đoán
│   ├── vicloabsa_model.pth  # Mô hình sau khi huấn luyện
│   ├── vocab.json           # Từ điển vocab sau khi huấn luyện
│   └── ViCloABSA/           # Thư mục chứa dataset (sau khi unzip)

```

### Bước 1: Chuẩn bị Dataset
Tải Dataset:
Truy cập link: https://github.com/quochungvnu24/ViCloABSA/raw/main/ViCloABSA.zip.
Tải file ZIP về và lưu vào thư mục Model_ML.
Giải nén Dataset 

### Cách chay:
pip3 install torch pandas  (Cài đặt thư viện)
pip install scikit-learn
cd Model_ML
python train.py
### Cách test
python predict.py " sản phẩm tệ"
python test_all.py (cd Model_ML)


